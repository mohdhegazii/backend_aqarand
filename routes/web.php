<?php

use App\Http\Controllers\Admin\AmenityController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeveloperController;
use App\Http\Controllers\Admin\DistrictController;
use App\Http\Controllers\Admin\FeaturedPlaceController;
use App\Http\Controllers\Admin\ListingController;
use App\Http\Controllers\Admin\LocationHelperController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\PropertyModelController;
use App\Http\Controllers\Admin\PropertyTypeController;
use App\Http\Controllers\Admin\RegionController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\UnitTypeController;
use App\Models\Developer;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Fallback for missing storage symlink - placed globally to avoid middleware interference
Route::get('media-fallback/{path}', [\App\Http\Controllers\StorageController::class, 'serve'])
    ->where('path', '.*')
    ->name('media.fallback');

// Shared route definitions to avoid duplicating default and localized groups
$registerAdminRoutes = function (string $namePrefix = 'admin.'): void {
    Route::group([
        'prefix' => 'admin',
        'as' => $namePrefix,
        'middleware' => ['auth', 'is_admin', SubstituteBindings::class],
    ], function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Location Helpers
        Route::get('locations/search', [LocationHelperController::class, 'search'])->name('locations.search');
        Route::get('locations/regions/{country}', [LocationHelperController::class, 'getRegions']);
        Route::get('locations/cities/{region}', [LocationHelperController::class, 'getCities']);
        Route::get('locations/districts/{city}', [LocationHelperController::class, 'getDistricts']);
        Route::get('locations/districts/{id}/projects', [LocationHelperController::class, 'getProjects']);

        // Lookups
        Route::get('lookups/developers', [\App\Http\Controllers\Admin\DeveloperLookupController::class, 'index'])->name('lookups.developers.index');
        Route::get('lookups/projects', [\App\Http\Controllers\Admin\ProjectLookupController::class, 'index'])->name('lookups.projects.index');
        Route::get('lookups/units', [\App\Http\Controllers\Admin\UnitLookupController::class, 'index'])->name('lookups.units.index');
        Route::get('lookups/property-models', [\App\Http\Controllers\Admin\PropertyModelLookupController::class, 'index'])->name('lookups.property_models.index');
        Route::get('lookups/unit-types', [\App\Http\Controllers\Admin\UnitTypeLookupController::class, 'index'])->name('lookups.unit_types.index');

        // Location Polygons (Map)
        Route::get('location-polygons', [\App\Http\Controllers\Admin\LocationPolygonController::class, 'index'])->name('location-polygons');

        Route::post('countries/bulk', [CountryController::class, 'bulk'])->name('countries.bulk');
        Route::resource('countries', CountryController::class);

        Route::post('regions/bulk', [RegionController::class, 'bulk'])->name('regions.bulk');
        Route::resource('regions', RegionController::class);

        Route::post('cities/bulk', [CityController::class, 'bulk'])->name('cities.bulk');
        Route::resource('cities', CityController::class);

        Route::post('districts/bulk', [DistrictController::class, 'bulk'])->name('districts.bulk');
        Route::resource('districts', DistrictController::class);

        Route::post('property-types/bulk', [PropertyTypeController::class, 'bulk'])->name('property-types.bulk');
        Route::resource('property-types', PropertyTypeController::class)
            ->parameters(['property-types' => 'propertyType']);

        Route::post('unit-types/bulk', [UnitTypeController::class, 'bulk'])->name('unit-types.bulk');
        Route::resource('unit-types', UnitTypeController::class)
            ->parameters(['unit-types' => 'unitType']);

        Route::post('amenities/bulk', [AmenityController::class, 'bulk'])->name('amenities.bulk');
        Route::resource('amenities', AmenityController::class);

        Route::post('developers/bulk', [DeveloperController::class, 'bulk'])->name('developers.bulk');
        Route::resource('developers', DeveloperController::class);

        Route::post('categories/bulk', [CategoryController::class, 'bulk'])->name('categories.bulk');
        Route::resource('categories', CategoryController::class)
            ->parameters(['categories' => 'category']);

        Route::resource('amenity-categories', \App\Http\Controllers\Admin\AmenityCategoryController::class)
            ->parameters(['amenity-categories' => 'amenityCategory']);

        // Phase 3 Resources
        Route::get('location-search', [\App\Http\Controllers\Admin\LocationSearchController::class, 'search'])->name('location.search');
        Route::resource('projects', ProjectController::class);
        Route::resource('property-models', PropertyModelController::class)
            ->parameters(['property-models' => 'propertyModel']);
        Route::resource('units', UnitController::class);
        Route::resource('listings', ListingController::class);

        // Featured Places Routes
        Route::get('featured-places', [FeaturedPlaceController::class, 'index'])->name('featured-places.index');
        Route::post('featured-places/main-categories', [FeaturedPlaceController::class, 'storeMainCategory'])->name('featured-places.main-categories.store');
        Route::put('featured-places/main-categories/{id}', [FeaturedPlaceController::class, 'updateMainCategory'])->name('featured-places.main-categories.update');
        Route::delete('featured-places/main-categories/{id}', [FeaturedPlaceController::class, 'destroyMainCategory'])->name('featured-places.main-categories.destroy');

        Route::post('featured-places/sub-categories', [FeaturedPlaceController::class, 'storeSubCategory'])->name('featured-places.sub-categories.store');
        Route::put('featured-places/sub-categories/{id}', [FeaturedPlaceController::class, 'updateSubCategory'])->name('featured-places.sub-categories.update');
        Route::delete('featured-places/sub-categories/{id}', [FeaturedPlaceController::class, 'destroySubCategory'])->name('featured-places.sub-categories.destroy');

        Route::post('featured-places/places', [FeaturedPlaceController::class, 'storePlace'])->name('featured-places.places.store');
        Route::put('featured-places/places/{id}', [FeaturedPlaceController::class, 'updatePlace'])->name('featured-places.places.update');
        Route::delete('featured-places/places/{id}', [FeaturedPlaceController::class, 'destroyPlace'])->name('featured-places.places.destroy');


        // Media Manager
        Route::resource('media', \App\Http\Controllers\Admin\MediaController::class)
            ->only(['index', 'show', 'update', 'destroy'])
            ->parameters(['media' => 'mediaFile']);

        Route::get('file-manager', [\App\Http\Controllers\Admin\MediaController::class, 'index'])->name('file_manager');

        // Project Media Upload (Test Route)
        Route::post('projects/{project}/media', [\App\Http\Controllers\Admin\ProjectController::class, 'uploadMedia'])->name('projects.media.store');

        // Secure File Download
        Route::get('media/download/{mediaFile}', [\App\Http\Controllers\Admin\MediaController::class, 'download'])->name('media.download');

        // Load separate Admin Routes
        require __DIR__.'/admin.php';
    });
};

$registerPublicRoutes = function (string $namePrefix = ''): void {
    Route::get('/', function () use ($namePrefix) {
        if (Auth::check()) {
            if (Auth::user()->is_admin) {
                return redirect()->route($namePrefix.'admin.dashboard');
            }

            return redirect()->route($namePrefix.'dashboard');
        }

        return view('welcome-public');
    })->name($namePrefix.'home');

    Route::get('/projects/{slug}', [\App\Http\Controllers\Frontend\ProjectController::class, 'show'])->name($namePrefix.'projects.show');
    Route::get('/units/{id}', [\App\Http\Controllers\Frontend\UnitController::class, 'show'])->name($namePrefix.'units.show');

    Route::middleware(['auth'])->group(function () use ($namePrefix) {
        Route::get('/dashboard', function () use ($namePrefix) {
            if (Auth::user()->is_admin) {
                return redirect()->route($namePrefix.'admin.dashboard');
            }

            return view('dashboard');
        })->name($namePrefix.'dashboard');
    });
};

// Default Arabic routes (no locale prefix)
Route::group([
    'middleware' => ['web', 'set.locale'],
], function () use ($registerPublicRoutes, $registerAdminRoutes) {
    // Fallback for missing storage symlink
    Route::get('media-fallback/{path}', [\App\Http\Controllers\StorageController::class, 'serve'])
        ->where('path', '.*')
        ->name('media.fallback');

    $registerPublicRoutes();

    // Removed the problematic lang/{targetLocale} route

    $registerAdminRoutes('admin.');

    // Breeze Authentication Routes
    if (file_exists(__DIR__.'/auth.php')) {
        require __DIR__.'/auth.php';
    }
});

// Localized routes for non-default locales
Route::group([
    'prefix' => '{locale}',
    'as' => 'localized.',
    'middleware' => ['web', 'set.locale'],
    // Ensure we strictly match supported locales (en) and prevent 'ar'
    'where' => ['locale' => '^(?!ar$)[a-zA-Z_]{2,5}$'],
], function () use ($registerPublicRoutes, $registerAdminRoutes) {
    $registerPublicRoutes('localized.');

    $registerAdminRoutes('localized.admin.');

    // Breeze Authentication Routes (localized)
    if (file_exists(__DIR__.'/auth.php')) {
        require __DIR__.'/auth.php';
    }
});

if (config('app.debug')) {
    Route::middleware(['web', 'auth'])->get('/debug/developer-logo/{id}', function ($id) {
        $developer = Developer::findOrFail($id);

        $raw = $developer->logo_path ?? $developer->logo ?? $developer->logo_url ?? null;

        $info = [
            'developer_id' => $developer->id,
            'raw_value' => $raw,
            'public_path' => public_path(),
            'storage_public_root' => config('filesystems.disks.public.root'),
            'exists_on_storage' => $raw ? \Storage::disk('public')->exists($raw) : null,
            'storage_url_if_any' => $raw && \Storage::disk('public')->exists($raw)
                ? \Storage::disk('public')->url($raw)
                : null,
            'public_storage_path' => $raw ? public_path('storage/' . ltrim($raw, '/')) : null,
            'exists_on_public_storage' => $raw ? file_exists(public_path('storage/' . ltrim($raw, '/'))) : null,
        ];

        return response()->json($info);
    });
}
