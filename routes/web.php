<?php

use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\RegionController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\DistrictController;
use App\Http\Controllers\Admin\PropertyTypeController;
use App\Http\Controllers\Admin\UnitTypeController;
use App\Http\Controllers\Admin\AmenityController;
use App\Http\Controllers\Admin\DeveloperController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\LocationHelperController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\PropertyModelController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\ListingController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->is_admin) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('dashboard');
    }
    return view('welcome-public');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        if (Auth::user()->is_admin) {
             return redirect()->route('admin.dashboard');
        }
        return view('dashboard');
    })->name('dashboard');
});

Route::get('lang/{locale}', [LanguageController::class, 'switch'])
    ->name('lang.switch')
    ->whereIn('locale', ['en', 'ar']);

Route::get('/admin', function () {
    return redirect()->route('admin.dashboard');
});

Route::group([
    'prefix' => 'admin',
    'as' => 'admin.',
    'middleware' => ['web', 'auth', 'is_admin', SubstituteBindings::class],
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
});

// Breeze Authentication Routes
if (file_exists(__DIR__.'/auth.php')) {
    require __DIR__.'/auth.php';
}
