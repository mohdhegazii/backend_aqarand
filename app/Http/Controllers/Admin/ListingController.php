<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        $query = Listing::with(['unit.project']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $listings = $query->latest()->paginate(10);

        return view('admin.listings.index', compact('listings'));
    }

    public function create()
    {
        // Removed heavy load of units.
        // User will search for unit via AJAX.

        return view('admin.listings.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id|unique:listings,unit_id',
            'listing_type' => 'required|in:primary,resale,rental',
            'title_en' => 'required|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'status' => 'required|in:draft,pending,published,hidden,sold,rented,expired',
            'slug_en' => 'nullable|string|unique:listings,slug_en',
            'slug_ar' => 'nullable|string|unique:listings,slug_ar',
            'seo_title_en' => 'nullable|string|max:255',
            'seo_description_en' => 'nullable|string',
        ]);

        if (empty($validated['slug_en'])) {
            $validated['slug_en'] = Str::slug($validated['title_en']);
        }

        $listing = Listing::create($request->except('slug_en', 'slug_ar') + [
            'title' => $validated['title_en'], // legacy
            'slug' => $validated['slug_en'], // legacy
            'slug_en' => $validated['slug_en'],
            'slug_ar' => $validated['slug_ar'] ?? ($request->has('title_ar') ? Str::slug($request->title_ar) : null),
            'is_featured' => $request->has('is_featured'),
            'published_at' => $request->status === 'published' ? now() : null,
        ]);

        return redirect()->route($this->adminRoutePrefix().'listings.index')
            ->with('success', __('admin.created_successfully'));
    }

    public function edit(Listing $listing)
    {
        // Removed heavy load of units.
        return view('admin.listings.edit', compact('listing'));
    }

    public function update(Request $request, Listing $listing)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id|unique:listings,unit_id,' . $listing->id,
            'listing_type' => 'required|in:primary,resale,rental',
            'title_en' => 'required|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'status' => 'required|in:draft,pending,published,hidden,sold,rented,expired',
            'slug_en' => 'nullable|string|unique:listings,slug_en,' . $listing->id,
        ]);

        if (empty($validated['slug_en'])) {
            $validated['slug_en'] = Str::slug($validated['title_en']);
        }

        $data = $request->except('slug_en', 'slug_ar');
        $data['slug_en'] = $validated['slug_en'];
        $data['slug_ar'] = $request->slug_ar ?? ($request->has('title_ar') ? Str::slug($request->title_ar) : null);
        $data['is_featured'] = $request->has('is_featured');

        if ($request->status === 'published' && !$listing->published_at) {
            $data['published_at'] = now();
        }

        $listing->update($data);

        return redirect()->route($this->adminRoutePrefix().'listings.index')
            ->with('success', __('admin.updated_successfully'));
    }

    public function destroy(Listing $listing)
    {
        $listing->delete();
        return redirect()->route($this->adminRoutePrefix().'listings.index')
            ->with('success', __('admin.deleted_successfully'));
    }
}
