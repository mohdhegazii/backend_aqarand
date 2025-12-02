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

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name_en', 'like', "%$search%")
                  ->orWhere('name_local', 'like', "%$search%")
                  ->orWhere('code', 'like', "%$search%");
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
        ]);

        Country::create($validated);

        return redirect()->route('admin.countries.index')
            ->with('success', __('admin.save') . ' ' . __('admin.country'));
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
        ]);

        $country->update($validated);

        return redirect()->route('admin.countries.index')
            ->with('success', __('admin.save') . ' ' . __('admin.country'));
    }

    public function destroy(Country $country)
    {
        $country->delete();

        return redirect()->route('admin.countries.index')
            ->with('success', __('admin.delete') . ' ' . __('admin.country'));
    }
}
