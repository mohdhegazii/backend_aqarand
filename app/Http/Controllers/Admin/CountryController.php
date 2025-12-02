<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function index(Request $request)
    {
        $query = Country::query();

        // Default to active only
        if (!$request->has('filter') || $request->filter === 'active') {
             $query->where('is_active', true);
        } elseif ($request->filter === 'inactive') {
             $query->where('is_active', false);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name_en', 'like', "%$search%")
                  ->orWhere('name_local', 'like', "%$search%")
                  ->orWhere('code', 'like', "%$search%");
            });
        }

        $countries = $query->paginate(10);

        return view('admin.countries.index', compact('countries'));
    }

    public function create()
    {
        return view('admin.countries.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:3|unique:countries,code',
            'name_en' => 'required|string|max:100',
            'name_local' => 'required|string|max:100',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        Country::create($validated);

        return redirect()->route('admin.countries.index', ['locale' => app()->getLocale()])
            ->with('success', __('admin.created_successfully'));
    }

    public function show(Country $country)
    {
        return view('admin.countries.show', compact('country'));
    }

    public function edit(Country $country)
    {
        return view('admin.countries.edit', compact('country'));
    }

    public function update(Request $request, Country $country)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:3|unique:countries,code,' . $country->id,
            'name_en' => 'required|string|max:100',
            'name_local' => 'required|string|max:100',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $country->update($validated);

        return redirect()->route('admin.countries.index', ['locale' => app()->getLocale()])
            ->with('success', __('admin.updated_successfully'));
    }

    public function destroy(Country $country)
    {
        // Soft delete implementation
        $country->update(['is_active' => false]);

        return redirect()->route('admin.countries.index', ['locale' => app()->getLocale()])
            ->with('success', __('admin.deleted_successfully'));
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:countries,id',
            'action' => 'required|in:activate,deactivate',
        ]);

        $isActive = $request->action === 'activate';
        Country::whereIn('id', $request->ids)->update(['is_active' => $isActive]);

        return redirect()->back()->with('success', __('admin.bulk_action_success'));
    }
}
