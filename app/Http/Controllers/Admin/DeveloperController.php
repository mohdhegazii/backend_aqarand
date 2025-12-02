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
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('name_en', 'like', "%$search%")
                  ->orWhere('name_ar', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%");
            });
        }

        $developers = $query->paginate(10);

        return view('admin.developers.index', compact('developers'));
    }

    public function create()
    {
        return view('admin.developers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:150',
            'name_ar' => 'required|string|max:150',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'website_url' => 'nullable|url|max:255',
            'is_active' => 'boolean',

            // Bilingual SEO
            'seo_meta.meta_title_en' => 'nullable|string|max:60',
            'seo_meta.meta_description_en' => 'nullable|string|max:160',
            'seo_meta.focus_keyphrase_en' => 'nullable|string|max:255',
            'seo_meta.meta_title_ar' => 'nullable|string|max:60',
            'seo_meta.meta_description_ar' => 'nullable|string|max:160',
            'seo_meta.focus_keyphrase_ar' => 'nullable|string|max:255',
        ]);

        $name = $validated['name_en'];
        $validated['name'] = $name; // Fallback
        $validated['slug'] = Str::slug($name);
        $validated['is_active'] = $request->has('is_active');

        if (Developer::where('slug', $validated['slug'])->exists()) {
             $validated['slug'] .= '-' . uniqid();
        }

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('developers', 'public');
            $validated['logo_path'] = $path;
            $validated['logo_url'] = Storage::url($path);
        }

        $developer = Developer::create($validated);

        if ($request->has('seo_meta')) {
            $developer->seoMeta()->create($request->input('seo_meta'));
        }

        return redirect()->route('admin.developers.index')
            ->with('success', __('admin.created_successfully'));
    }

    public function edit(Developer $developer)
    {
        return view('admin.developers.edit', compact('developer'));
    }

    public function update(Request $request, Developer $developer)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:150',
            'name_ar' => 'required|string|max:150',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'website_url' => 'nullable|url|max:255',
            'is_active' => 'boolean',

            // Bilingual SEO
            'seo_meta.meta_title_en' => 'nullable|string|max:60',
            'seo_meta.meta_description_en' => 'nullable|string|max:160',
            'seo_meta.focus_keyphrase_en' => 'nullable|string|max:255',
            'seo_meta.meta_title_ar' => 'nullable|string|max:60',
            'seo_meta.meta_description_ar' => 'nullable|string|max:160',
            'seo_meta.focus_keyphrase_ar' => 'nullable|string|max:255',
        ]);

        $name = $validated['name_en'];
        $validated['name'] = $name; // Fallback
        $slug = Str::slug($name);
        $validated['is_active'] = $request->has('is_active');

        if ($slug !== $developer->slug) {
             if (Developer::where('slug', $slug)->where('id', '!=', $developer->id)->exists()) {
                 $slug .= '-' . uniqid();
             }
             $validated['slug'] = $slug;
        }

        if ($request->hasFile('logo')) {
            if ($developer->logo_path) {
                Storage::disk('public')->delete($developer->logo_path);
            }
            $path = $request->file('logo')->store('developers', 'public');
            $validated['logo_path'] = $path;
            $validated['logo_url'] = Storage::url($path);
        }

        $developer->update($validated);

        if ($request->has('seo_meta')) {
            $developer->seoMeta()->updateOrCreate(
                [],
                $request->input('seo_meta')
            );
        }

        return redirect()->route('admin.developers.index')
            ->with('success', __('admin.updated_successfully'));
    }

    public function destroy(Developer $developer)
    {
        // Soft delete implementation
        $developer->update(['is_active' => false]);

        return redirect()->route('admin.developers.index')
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
