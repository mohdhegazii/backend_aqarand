<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyModel extends Model
{
    use HasFactory;

    protected $table = 'property_models';

    protected $fillable = [
        'project_id',
        'unit_type_id',
        'name',
        'name_en',
        'name_ar',
        'code',
        'description',
        'description_en',
        'description_ar',
        'bedrooms',
        'bathrooms',
        'min_bua',
        'max_bua',
        'min_land_area',
        'max_land_area',
        'min_price',
        'max_price',
        'floorplan_2d_url',
        'floorplan_3d_url',
        'gallery',
        'seo_slug',
        'seo_slug_en',
        'seo_slug_ar',
        'meta_title',
        'meta_title_en',
        'meta_title_ar',
        'meta_description',
        'meta_description_en',
        'meta_description_ar',
        'is_active',
    ];

    protected $casts = [
        'gallery' => 'array',
        'is_active' => 'boolean',
        'min_bua' => 'decimal:2',
        'max_bua' => 'decimal:2',
        'min_land_area' => 'decimal:2',
        'max_land_area' => 'decimal:2',
        'min_price' => 'decimal:2',
        'max_price' => 'decimal:2',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function unitType()
    {
        return $this->belongsTo(UnitType::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function getDisplayNameAttribute()
    {
        $locale = app()->getLocale();
        if ($locale === 'ar' && !empty($this->name_ar)) {
            return $this->name_ar;
        }
        return $this->name_en ?? $this->name;
    }

    public function getFrontendUrl(string $locale = 'en'): string
    {
        // Assuming models are nested under projects or independent
        $slug = $locale === 'ar' ? ($this->seo_slug_ar ?? $this->seo_slug_en) : ($this->seo_slug_en);
        return "/{$locale}/models/{$slug}";
    }
}
