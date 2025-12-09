<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeaturedPlaceMainCategory extends Model
{
    protected $table = 'featured_place_main_categories';

    protected $fillable = [
        'name_ar',
        'name_en',
        'slug',
        'icon_name',
        'pin_icon',
        'description_ar',
        'description_en',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function subCategories(): HasMany
    {
        return $this->hasMany(FeaturedPlaceSubCategory::class, 'main_category_id');
    }

    public function places(): HasMany
    {
        return $this->hasMany(FeaturedPlace::class, 'main_category_id');
    }

    public function getNameAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en;
    }
}
