<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Developer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class DeveloperController extends Controller
{
    public function index(Request $request)
    {
        $query = Developer::query();

        // Default to active only
        if (!$request->has('filter') || $request->filter === 'active') {
             $query->where('is_active', true);
        } elseif ($request->filter === 'inactive') {
             $query->where('is_active', false);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            // Current search uses multi-column OR LIKE; consider migrating to fulltext in the future.
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('name_en', 'like', "%$search%")
                  ->orWhere('name_ar', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%");
            });
        }

        // Ensure we order by name for index usage, or other indexed columns
        $developers = $query->orderBy('name')->paginate(10);

        return view('admin.developers.index', compact('developers'));
    }

    public function create()
    {
        return view('admin.developers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:150', // Backward compat, can be derived
            'name_en' => 'required_without:name|string|max:150',
            'name_ar' => 'required_without:name|string|max:150',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'logo' => 'nullable|mimes:jpeg,png,jpg,webp,gif,svg|max:2048', // Legacy input
            'logo_media_id' => 'nullable|integer|exists:media_files,id', // New Media Manager input
            'website_url' => 'nullable|url|max:255',
            'is_active' => 'boolean',
            'seo_meta.focus_keyphrase' => 'nullable|string|max:255',
            'seo_meta.meta_title' => 'nullable|string|max:60',
            'seo_meta.meta_description' => 'nullable|string|max:160',
        ]);

        $name = $validated['name_en'] ?? $validated['name'];
        $validated['name'] = $name;
        $validated['slug'] = Str::slug($name);
        $validated['is_active'] = $request->has('is_active');

        if (Developer::where('slug', $validated['slug'])->exists()) {
             $validated['slug'] .= '-' . uniqid();
        }

        // Legacy Logo Handling
        if ($request->hasFile('logo')) {
            // Store in 'developers' folder on 'public' disk
            // resulting path will be like 'developers/filename.png'
            $path = $request->file('logo')->store('developers', 'public');

            // Save to compatible columns
            $validated['logo_path'] = $path;
        }

        // Always unset 'logo' to prevent SQL error if the column does not exist
        unset($validated['logo']);

        // Remove media_id from validated before create to avoid 'unknown column' error
        $logoMediaId = $validated['logo_media_id'] ?? null;
        unset($validated['logo_media_id']);

        if (isset($validated['website_url'])) {
            $validated['website'] = $validated['website_url'];
            unset($validated['website_url']);
        }

        $developer = Developer::create($validated);

        // Attach Media (Logo)
        if ($logoMediaId) {
            $developer->syncMedia($logoMediaId, 'logo');
        } elseif ($request->hasFile('logo')) {
            // If user used legacy upload, we could ideally create a media entry for it too
            // but for now, we just rely on legacy columns, OR we could migrate it immediately.
            // Let's stick to user request: "If not present but legacy logo column exists: Keep legacy behavior unchanged."
            // So we don't do anything extra here.
            // But if user removed logo, we should check logic.
            // Here it's create, so we are good.
        }

        if ($request->has('seo_meta')) {
            $developer->seoMeta()->create($request->input('seo_meta'));
        }

        return redirect()->route($this->adminRoutePrefix().'developers.index')
            ->with('success', __('admin.created_successfully'));
    }

    public function edit(Developer $developer)
    {
        return view('admin.developers.edit', compact('developer'));
    }

    public function update(Request $request, Developer $developer)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:150',
            'name_en' => 'required_without:name|string|max:150',
            'name_ar' => 'required_without:name|string|max:150',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'logo' => 'nullable|mimes:jpeg,png,jpg,webp,gif,svg|max:2048', // Legacy
            'logo_media_id' => 'nullable|integer|exists:media_files,id', // New
            'website_url' => 'nullable|url|max:255',
            'is_active' => 'boolean',
            'seo_meta.focus_keyphrase' => 'nullable|string|max:255',
            'seo_meta.meta_title' => 'nullable|string|max:60',
            'seo_meta.meta_description' => 'nullable|string|max:160',
        ]);

        $name = $validated['name_en'] ?? $validated['name'];
        $validated['name'] = $name;
        $slug = Str::slug($name);
        $validated['is_active'] = $request->has('is_active');

        if ($slug !== $developer->slug) {
             if (Developer::where('slug', $slug)->where('id', '!=', $developer->id)->exists()) {
                 $slug .= '-' . uniqid();
             }
             $validated['slug'] = $slug;
        }

        // Handle Media Manager Logo
        if ($request->has('logo_media_id')) {
            $logoMediaId = $request->input('logo_media_id');
            if ($logoMediaId) {
                $developer->syncMedia($logoMediaId, 'logo');
            } else {
                // If explicitly cleared (sent as null), detach
                // BUT we must be careful: if user didn't touch the media picker, does it send null?
                // The media picker component sends empty string or null if cleared.
                // If the user wants to keep existing logo, the form should be pre-filled.
                // If it is pre-filled, we get the ID.
                // If user removes it, we get null.
                // So yes, syncMedia handles single ID or empty array.
                // But wait, syncMedia([], 'logo') clears it.
                // syncMedia(null, 'logo') -> mediaIds = [null] -> loop -> if(mediaId) -> attach.
                // So syncMedia(null) effectively clears it because detach happens first.
                // Wait, let's check syncMedia implementation again.
                // $mediaIds = is_array($media) ? $media : [$media];
                // if media is null, $mediaIds = [null].
                // Loop: foreach([null] as $id) -> if(null) false.
                // So detach happens, but no attach. Correct.
                $developer->syncMedia($logoMediaId, 'logo');
            }
        }

        // Legacy Logo Handling
        // Only process legacy logo if user uploaded a file via legacy input
        if ($request->hasFile('logo')) {
            // Delete old logo if it exists
            $oldLogo = $developer->logo_path ?? $developer->logo ?? $developer->logo_url;
            if ($oldLogo) {
                // Check if it's a file on disk (not a URL)
                if (!Str::startsWith($oldLogo, ['http://', 'https://'])) {
                    Storage::disk('public')->delete($oldLogo);
                }
            }

            // Store new logo
            $path = $request->file('logo')->store('developers', 'public');
            $validated['logo_path'] = $path;

            // If we have a legacy file upload, we should probably clear the media link 'logo'
            // to favor the new file? Or let them coexist?
            // The model resolution logic prioritizes Media Manager.
            // So if user uploads legacy file but keeps old media link, the media link wins.
            // So we should detach media link if legacy file is uploaded.
            $developer->detachMedia('logo');
        }

        // Always unset 'logo' and 'logo_media_id' to prevent SQL error
        unset($validated['logo']);
        unset($validated['logo_media_id']);

        if (isset($validated['website_url'])) {
            $validated['website'] = $validated['website_url'];
            unset($validated['website_url']);
        }

        $developer->update($validated);

        if ($request->has('seo_meta')) {
            $developer->seoMeta()->updateOrCreate(
                [],
                $request->input('seo_meta')
            );
        }

        return redirect()->route($this->adminRoutePrefix().'developers.index')
            ->with('success', __('admin.updated_successfully'));
    }

    public function destroy(Developer $developer)
    {
        // Soft delete implementation
        $developer->update(['is_active' => false]);

        return redirect()->route($this->adminRoutePrefix().'developers.index')
            ->with('success', __('admin.deleted_successfully'));
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:developers,id',
            'action' => 'required|in:activate,deactivate',
        ]);

        $isActive = $request->action === 'activate';
        Developer::whereIn('id', $request->ids)->update(['is_active' => $isActive]);

        return redirect()->back()->with('success', __('admin.bulk_action_success'));
    }
}
