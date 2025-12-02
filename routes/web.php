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
use App\Http\Controllers\Admin\SegmentController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\LocationHelperController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->is_admin) {
            $locale = session('locale', config('app.locale', 'en'));
            return redirect()->route('admin.dashboard', ['locale' => $locale]);
        }
        return redirect()->route('dashboard');
    }
    return view('welcome-public');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        if (Auth::user()->is_admin) {
             $locale = session('locale', config('app.locale', 'en'));
             return redirect()->route('admin.dashboard', ['locale' => $locale]);
        }
        return view('dashboard');
    })->name('dashboard');
});

Route::get('lang/{locale}', [LanguageController::class, 'switch'])
    ->name('lang.switch')
    ->whereIn('locale', ['en', 'ar']);

Route::get('/admin', function () {
    return redirect()->route('admin.dashboard', ['locale' => 'en']);
});

Route::group([
    'prefix' => '{locale}/admin',
    'as' => 'admin.',
    'middleware' => ['web', 'auth', 'is_admin', 'setLocaleFromUrl', SubstituteBindings::class],
], function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Location Helpers
    Route::get('locations/countries/{id}', [LocationHelperController::class, 'getCountry']);
    Route::get('locations/regions/{id}', [LocationHelperController::class, 'getRegion']);
    Route::get('locations/cities/{id}', [LocationHelperController::class, 'getCity']);

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

    Route::post('segments/bulk', [SegmentController::class, 'bulk'])->name('segments.bulk');
    Route::resource('segments', SegmentController::class);

    Route::post('categories/bulk', [CategoryController::class, 'bulk'])->name('categories.bulk');
    Route::resource('categories', CategoryController::class);

    Route::resource('amenity-categories', \App\Http\Controllers\Admin\AmenityCategoryController::class)
        ->parameters(['amenity-categories' => 'amenityCategory']);
})->whereIn('locale', ['en', 'ar']);

// Breeze Authentication Routes
if (file_exists(__DIR__.'/auth.php')) {
    require __DIR__.'/auth.php';
}
