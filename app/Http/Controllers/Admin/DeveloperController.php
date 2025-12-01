<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Developer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DeveloperController extends Controller
{
    public function index(Request $request)
    {
        $query = Developer::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%");
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
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'logo_url' => 'nullable|url|max:255',
            'website_url' => 'nullable|url|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');

        // Check for uniqueness of the generated slug
        if (Developer::where('slug', $validated['slug'])->exists()) {
             return back()->withInput()->withErrors(['name' => 'Slug generated from Name already exists.']);
        }

        Developer::create($validated);

        return redirect()->route('admin.developers.index')
            ->with('success', __('admin.save') . ' ' . __('admin.developers'));
    }

    public function edit(Developer $developer)
    {
        return view('admin.developers.edit', compact('developer'));
    }

    public function update(Request $request, Developer $developer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'logo_url' => 'nullable|url|max:255',
            'website_url' => 'nullable|url|max:255',
            'is_active' => 'boolean',
        ]);

        $slug = Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');

        if ($slug !== $developer->slug) {
             // Check for uniqueness of the generated slug
             if (Developer::where('slug', $slug)->where('id', '!=', $developer->id)->exists()) {
                 return back()->withInput()->withErrors(['name' => 'Slug generated from Name already exists.']);
             }
             $validated['slug'] = $slug;
        }

        $developer->update($validated);

        return redirect()->route('admin.developers.index')
            ->with('success', __('admin.save') . ' ' . __('admin.developers'));
    }

    public function destroy(Developer $developer)
    {
        $developer->delete();

        return redirect()->route('admin.developers.index')
            ->with('success', __('admin.delete') . ' ' . __('admin.developers'));
    }
}
