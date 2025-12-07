<?php

use App\Http\Controllers\Admin\AmenityController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeveloperController;
use App\Http\Controllers\Admin\DistrictController;
use App\Http\Controllers\Admin\ListingController;
use App\Http\Controllers\Admin\LocationHelperController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\PropertyModelController;
use App\Http\Controllers\Admin\PropertyTypeController;
use App\Http\Controllers\Admin\RegionController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\UnitTypeController;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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
        Route::get('locations/countries/{id}', [LocationHelperController::class, 'getRegions']);
        Route::get('locations/regions/{id}', [LocationHelperController::class, 'getCities']);
        Route::get('locations/cities/{id}', [LocationHelperController::class, 'getDistricts']);

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

        // Media Manager
        Route::resource('media', \App\Http\Controllers\Admin\MediaController::class)
            ->only(['index', 'show', 'update', 'destroy'])
            ->parameters(['media' => 'mediaFile']);

        // Project Media Upload (Test Route)
        Route::post('projects/{project}/media', [\App\Http\Controllers\Admin\ProjectController::class, 'uploadMedia'])->name('projects.media.store');

        // Secure File Download
        Route::get('media/download/{mediaFile}', [\App\Http\Controllers\Admin\MediaController::class, 'download'])->name('media.download');

        // Map Boundaries
        Route::get('map/boundaries', [\App\Http\Controllers\Admin\MapController::class, 'boundaries'])->name('map.boundaries');
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
