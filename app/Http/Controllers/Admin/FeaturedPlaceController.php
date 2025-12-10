<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\FeaturedPlace;
use App\Models\FeaturedPlaceMainCategory;
use App\Models\FeaturedPlaceSubCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class FeaturedPlaceController extends Controller
{
    public function index()
    {
        $mainCategories = FeaturedPlaceMainCategory::orderBy('id', 'desc')->get();
        $subCategories = FeaturedPlaceSubCategory::with('mainCategory')->orderBy('id', 'desc')->get();
        $places = FeaturedPlace::with(['mainCategory', 'subCategory', 'city', 'district'])->orderBy('id', 'desc')->paginate(20);
        $countries = Country::where('is_active', true)->get();

        return view('admin.settings.featured_places.index', compact('mainCategories', 'subCategories', 'places', 'countries'));
    }

    // --- Main Categories ---
    public function storeMainCategory(Request $request)
    {
        $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'icon_name' => 'nullable|string|max:255',
        ]);

        FeaturedPlaceMainCategory::create([
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
            'slug' => Str::slug($request->name_en),
            'icon_name' => $request->icon_name,
            'description_ar' => $request->description_ar,
            'description_en' => $request->description_en,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->back()->with('success', __('admin.created_successfully'));
    }

    public function updateMainCategory(Request $request, $id)
    {
        $category = FeaturedPlaceMainCategory::findOrFail($id);

        $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'icon_name' => 'nullable|string|max:255',
        ]);

        $category->update([
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
            'slug' => Str::slug($request->name_en),
            'icon_name' => $request->icon_name,
            'description_ar' => $request->description_ar,
            'description_en' => $request->description_en,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->back()->with('success', __('admin.updated_successfully'));
    }

    public function destroyMainCategory($id)
    {
        $category = FeaturedPlaceMainCategory::findOrFail($id);
        $category->delete();
        return redirect()->back()->with('success', __('admin.deleted_successfully'));
    }

    // --- Sub Categories ---
    public function storeSubCategory(Request $request)
    {
        $request->validate([
            'main_category_id' => 'required|exists:featured_place_main_categories,id',
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
        ]);

        FeaturedPlaceSubCategory::create([
            'main_category_id' => $request->main_category_id,
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
            'slug' => Str::slug($request->name_en),
            'description_ar' => $request->description_ar,
            'description_en' => $request->description_en,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->back()->with('success', __('admin.created_successfully'));
    }

    public function updateSubCategory(Request $request, $id)
    {
        $subCategory = FeaturedPlaceSubCategory::findOrFail($id);

        $request->validate([
            'main_category_id' => 'required|exists:featured_place_main_categories,id',
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
        ]);

        $subCategory->update([
            'main_category_id' => $request->main_category_id,
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
            'slug' => Str::slug($request->name_en),
            'description_ar' => $request->description_ar,
            'description_en' => $request->description_en,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->back()->with('success', __('admin.updated_successfully'));
    }

    public function destroySubCategory($id)
    {
        $subCategory = FeaturedPlaceSubCategory::findOrFail($id);
        $subCategory->delete();
        return redirect()->back()->with('success', __('admin.deleted_successfully'));
    }

    // --- Featured Places ---
    public function storePlace(Request $request)
    {
        $request->validate([
            'main_category_id' => 'required|exists:featured_place_main_categories,id',
            'sub_category_id' => [
                'required',
                Rule::exists('featured_place_sub_categories', 'id')
                    ->where('main_category_id', $request->input('main_category_id')),
            ],
            'country_id' => 'required|exists:countries,id',
            'region_id' => 'required|exists:regions,id',
            'city_id' => 'required|exists:cities,id',
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'point_lat' => 'required|numeric',
            'point_lng' => 'required|numeric',
        ]);

        $data = $request->except(['_token', 'boundary_geojson', 'polygon_geojson']);

        // Handle polygon
        if ($request->filled('polygon_geojson')) {
             $data['polygon_geojson'] = json_decode($request->polygon_geojson, true);
        } elseif ($request->filled('boundary_geojson')) {
            // Map component might send boundary_geojson
             $data['polygon_geojson'] = json_decode($request->boundary_geojson, true);
        }

        $data['is_active'] = $request->has('is_active');
        $data['sub_category_id'] = $request->input('sub_category_id');
        $data['district_id'] = $request->input('district_id') ?: null;

        FeaturedPlace::create($data);

        return redirect()->route('admin.featured-places.index', ['tab' => 'places'])
            ->with('success', __('admin.created_successfully'));
    }

    public function updatePlace(Request $request, $id)
    {
        $place = FeaturedPlace::findOrFail($id);

        $request->validate([
            'main_category_id' => 'required|exists:featured_place_main_categories,id',
            'sub_category_id' => [
                'required',
                Rule::exists('featured_place_sub_categories', 'id')
                    ->where('main_category_id', $request->input('main_category_id')),
            ],
            'country_id' => 'required|exists:countries,id',
            'region_id' => 'required|exists:regions,id',
            'city_id' => 'required|exists:cities,id',
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'point_lat' => 'required|numeric',
            'point_lng' => 'required|numeric',
        ]);

        $data = $request->except(['_token', '_method', 'boundary_geojson', 'polygon_geojson']);

        // Handle polygon
        if ($request->filled('polygon_geojson')) {
             $data['polygon_geojson'] = json_decode($request->polygon_geojson, true);
        } elseif ($request->filled('boundary_geojson')) {
             $data['polygon_geojson'] = json_decode($request->boundary_geojson, true);
        } else {
             // If empty but previously had one, we might want to keep it or clear it.
             // Logic: if hidden input is present but empty, it might mean clear.
             // But usually map component populates it.
             // If user cleared the shape, it should be empty.
             $data['polygon_geojson'] = null;
        }

        $data['is_active'] = $request->has('is_active');
        $data['sub_category_id'] = $request->input('sub_category_id');
        $data['district_id'] = $request->input('district_id') ?: null;

        $place->update($data);

        return redirect()->route('admin.featured-places.index', ['tab' => 'places'])
            ->with('success', __('admin.updated_successfully'));
    }

    public function destroyPlace($id)
    {
        $place = FeaturedPlace::findOrFail($id);
        $place->delete();
        return redirect()->route('admin.featured-places.index', ['tab' => 'places'])
            ->with('success', __('admin.deleted_successfully'));
    }
}
