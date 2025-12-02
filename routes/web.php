<?php

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
use App\Http\Controllers\Admin\SegmentController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\LocationHelperController;
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

Route::middleware(['auth', 'is_admin'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Location Helpers
        Route::get('locations/countries/{id}', [LocationHelperController::class, 'getCountry']);
        Route::get('locations/regions/{id}', [LocationHelperController::class, 'getRegion']);
        Route::get('locations/cities/{id}', [LocationHelperController::class, 'getCity']);

        // Bulk Routes (Defined explicitly before resources)
        Route::post('countries/bulk', [CountryController::class, 'bulk'])->name('countries.bulk');
        Route::post('regions/bulk', [RegionController::class, 'bulk'])->name('regions.bulk');
        Route::post('cities/bulk', [CityController::class, 'bulk'])->name('cities.bulk');
        Route::post('districts/bulk', [DistrictController::class, 'bulk'])->name('districts.bulk');
        Route::post('property-types/bulk', [PropertyTypeController::class, 'bulk'])->name('property-types.bulk');
        Route::post('unit-types/bulk', [UnitTypeController::class, 'bulk'])->name('unit-types.bulk');
        Route::post('amenities/bulk', [AmenityController::class, 'bulk'])->name('amenities.bulk');
        Route::post('developers/bulk', [DeveloperController::class, 'bulk'])->name('developers.bulk');
        Route::post('segments/bulk', [SegmentController::class, 'bulk'])->name('segments.bulk');
        Route::post('categories/bulk', [CategoryController::class, 'bulk'])->name('categories.bulk');

        // Resources
        Route::resource('countries', CountryController::class);
        Route::resource('regions', RegionController::class);
        Route::resource('cities', CityController::class);
        Route::resource('districts', DistrictController::class);
        Route::resource('property-types', PropertyTypeController::class);
        Route::resource('unit-types', UnitTypeController::class);
        Route::resource('amenities', AmenityController::class);
        Route::resource('developers', DeveloperController::class);
        Route::resource('segments', SegmentController::class);
        Route::resource('categories', CategoryController::class);
    });

// Breeze Authentication Routes
if (file_exists(__DIR__.'/auth.php')) {
    require __DIR__.'/auth.php';
}
