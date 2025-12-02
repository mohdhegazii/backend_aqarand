<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Segment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', true)->with('segment')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $segments = Segment::where('is_active', true)->get();
        return view('admin.categories.create', compact('segments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'segment_id' => 'required|exists:segments,id',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('lookups', 'public');
            $data['image_path'] = $path;
        }

        Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', __('admin.created_successfully'));
    }

    public function edit(Category $category)
    {
        $segments = Segment::where('is_active', true)->get();
        return view('admin.categories.edit', compact('category', 'segments'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'segment_id' => 'required|exists:segments,id',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug,' . $category->id,
            'image' => 'nullable|image|max:2048',
        ]);

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            if ($category->image_path) {
                Storage::disk('public')->delete($category->image_path);
            }
            $path = $request->file('image')->store('lookups', 'public');
            $data['image_path'] = $path;
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', __('admin.updated_successfully'));
    }

    public function destroy(Category $category)
    {
        $category->update(['is_active' => false]);
        return redirect()->route('admin.categories.index')->with('success', __('admin.deleted_successfully'));
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:categories,id',
            'action' => 'required|in:activate,deactivate',
        ]);

        $isActive = $request->action === 'activate';
        Category::whereIn('id', $request->ids)->update(['is_active' => $isActive]);

        return redirect()->back()->with('success', __('admin.bulk_action_success'));
    }
}
