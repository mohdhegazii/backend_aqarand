<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeaturedPlace extends Model
{
    protected $table = 'featured_places';

    protected $fillable = [
        'main_category_id',
        'sub_category_id',
        'country_id',
        'region_id',
        'city_id',
        'district_id',
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'pin_icon',
        'point_lat',
        'point_lng',
        'polygon_geojson',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'point_lat' => 'decimal:7',
        'point_lng' => 'decimal:7',
        'polygon_geojson' => 'array',
    ];

    public function mainCategory(): BelongsTo
    {
        return $this->belongsTo(FeaturedPlaceMainCategory::class, 'main_category_id');
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(FeaturedPlaceSubCategory::class, 'sub_category_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function getNameAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en;
    }
}
