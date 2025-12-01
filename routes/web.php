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

Route::get('/', function () {
    return view('welcome');
});

Route::get('lang/{locale}', [LanguageController::class, 'switch'])
    ->name('lang.switch')
    ->whereIn('locale', ['en', 'ar']);

Route::middleware(['auth', 'is_admin'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('countries', CountryController::class);
        Route::resource('regions', RegionController::class);
        Route::resource('cities', CityController::class);
        Route::resource('districts', DistrictController::class);

        Route::resource('property-types', PropertyTypeController::class);
        Route::resource('unit-types', UnitTypeController::class);
        Route::resource('amenities', AmenityController::class);
        Route::resource('developers', DeveloperController::class);
    });

// Breeze Authentication Routes
if (file_exists(__DIR__.'/auth.php')) {
    require __DIR__.'/auth.php';
}
