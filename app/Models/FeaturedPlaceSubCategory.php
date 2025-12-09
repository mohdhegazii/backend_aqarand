<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeaturedPlaceSubCategory extends Model
{
    protected $table = 'featured_place_sub_categories';

    protected $fillable = [
        'main_category_id',
        'name_ar',
        'name_en',
        'slug',
        'description_ar',
        'description_en',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function mainCategory(): BelongsTo
    {
        return $this->belongsTo(FeaturedPlaceMainCategory::class, 'main_category_id');
    }

    public function places(): HasMany
    {
        return $this->hasMany(FeaturedPlace::class, 'sub_category_id');
    }

    public function getNameAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en;
    }
}
