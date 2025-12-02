<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Models\AmenityCategory;
use App\Models\Country;
use App\Models\Region;
use App\Models\City;
use App\Models\District;
use App\Models\PropertyType;
use App\Models\UnitType;
use App\Models\Amenity;
use App\Models\Developer;
use App\Models\Category;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::model('amenityCategory', AmenityCategory::class);
        Route::model('country', Country::class);
        Route::model('region', Region::class);
        Route::model('city', City::class);
        Route::model('district', District::class);
        Route::model('propertyType', PropertyType::class);
        Route::model('unitType', UnitType::class);
        Route::model('amenity', Amenity::class);
        Route::model('developer', Developer::class);
        Route::model('category', Category::class);
    }
}
