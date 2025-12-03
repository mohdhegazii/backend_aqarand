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

// Public Welcome
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

// Admin Locale Switching
Route::get('lang/{locale}', [LanguageController::class, 'switch'])
    ->name('lang.switch')
    ->whereIn('locale', ['en', 'ar']);


// ADMIN GROUP with Optional Locale Prefix
// Matches /admin (AR default) and /en/admin (EN)
Route::group([
    'prefix' => '{locale?}/admin',
    'where' => ['locale' => 'en'], // Only allow 'en' as prefix, empty implies 'ar'
    'as' => 'admin.',
    'middleware' => ['web', 'setLocaleFromUrl', 'auth', 'is_admin', SubstituteBindings::class],
], function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Location Helpers
    Route::get('locations/countries/{id}', [LocationHelperController::class, 'getCountry']);
    Route::get('locations/regions/{id}', [LocationHelperController::class, 'getRegion']);
    Route::get('locations/cities/{id}', [LocationHelperController::class, 'getCity']);
    // New: Global Search
    Route::get('locations/global-search', [LocationHelperController::class, 'searchLocations']);

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
});

// Admin Root Redirection
Route::get('/admin', function () {
    return redirect()->route('admin.dashboard');
});
// Handle /en/admin redirect
Route::get('/en/admin', function () {
    return redirect()->route('admin.dashboard', ['locale' => 'en']);
});


// Breeze Authentication Routes
if (file_exists(__DIR__.'/auth.php')) {
    require __DIR__.'/auth.php';
}
