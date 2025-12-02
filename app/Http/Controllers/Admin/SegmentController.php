<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Segment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class SegmentController extends Controller
{
    public function index()
    {
        $segments = Segment::where('is_active', true)->get();
        return view('admin.segments.index', compact('segments'));
    }

    public function create()
    {
        return view('admin.segments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|max:2048',
        ]);

        $validated['is_active'] = $request->has('is_active');

        // Generate slug
        $baseName = $validated['name_en'] ?: $validated['name_ar'];
        $slug = Str::slug($baseName);
        $originalSlug = $slug;
        $counter = 1;
        while (Segment::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }
        $validated['slug'] = $slug;

        // Handle Image
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('segments', 'public');
            $validated['image_path'] = $path;
        }

        Segment::create($validated);

        return redirect()->route('admin.segments.index')->with('success', __('admin.created_successfully'));
    }

    public function edit(Segment $segment)
    {
        return view('admin.segments.edit', compact('segment'));
    }

    public function update(Request $request, Segment $segment)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|max:2048',
        ]);

        $validated['is_active'] = $request->has('is_active');

        // Re-generate slug if name_en changes
        if ($segment->name_en !== $validated['name_en']) {
             $baseName = $validated['name_en'] ?: $validated['name_ar'];
             $slug = Str::slug($baseName);
             $originalSlug = $slug;
             $counter = 1;
             // Exclude current segment from uniqueness check
             while (Segment::where('slug', $slug)->where('id', '!=', $segment->id)->exists()) {
                 $slug = $originalSlug . '-' . $counter++;
             }
             $validated['slug'] = $slug;
        }

        // Handle Image
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($segment->image_path && Storage::disk('public')->exists($segment->image_path)) {
                Storage::disk('public')->delete($segment->image_path);
            }

            $path = $request->file('image')->store('segments', 'public');
            $validated['image_path'] = $path;
        }

        $segment->update($validated);

        return redirect()->route('admin.segments.index')->with('success', __('admin.updated_successfully'));
    }

    public function destroy(Segment $segment)
    {
        $segment->update(['is_active' => false]);
        return redirect()->route('admin.segments.index')->with('success', __('admin.deleted_successfully'));
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:segments,id',
            'action' => 'required|in:activate,deactivate',
        ]);

        $isActive = $request->action === 'activate';
        Segment::whereIn('id', $request->ids)->update(['is_active' => $isActive]);

        return redirect()->back()->with('success', __('admin.bulk_action_success'));
    }
}
