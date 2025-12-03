<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory;

    protected $table = 'projects';

    protected $fillable = [
        'developer_id',
        'country_id',
        'region_id',
        'city_id',
        'district_id',
        'name',
        'name_en',
        'name_ar',
        'slug',
        'seo_slug_en',
        'seo_slug_ar',
        'tagline',
        'tagline_en',
        'tagline_ar',
        'description_long',
        'description_en',
        'description_ar',
        'status',
        'delivery_year',
        'total_area',
        'built_up_ratio',
        'total_units',
        'min_price',
        'max_price',
        'min_bua',
        'max_bua',
        'lat',
        'lng',
        'address_text',
        'address_en',
        'address_ar',
        'brochure_url',
        'hero_image_url',
        'gallery',
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
        'total_area' => 'decimal:2',
        'built_up_ratio' => 'decimal:2',
        'min_price' => 'decimal:2',
        'max_price' => 'decimal:2',
        'min_bua' => 'decimal:2',
        'max_bua' => 'decimal:2',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'delivery_year' => 'integer',
        'total_units' => 'integer',
    ];

    // Status Hierarchy: Higher index = Higher status
    public const STATUS_HIERARCHY = [
        'new_launch' => 0,
        'off_plan' => 1,
        'under_construction' => 2,
        'ready_to_move' => 3,
        'livable' => 4,
    ];

    public function developer()
    {
        return $this->belongsTo(Developer::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function propertyModels()
    {
        return $this->hasMany(PropertyModel::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function listings()
    {
        return $this->hasManyThrough(Listing::class, Unit::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'project_amenity', 'project_id', 'amenity_id');
    }

    public function getDisplayNameAttribute()
    {
        $locale = app()->getLocale();
        if ($locale === 'ar' && !empty($this->name_ar)) {
            return $this->name_ar;
        }
        return $this->name_en ?? $this->name;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInLocation($query, $cityId = null, $districtId = null)
    {
        if ($districtId) {
            return $query->where('district_id', $districtId);
        }
        if ($cityId) {
            return $query->where('city_id', $cityId);
        }
        return $query;
    }

    public function getFrontendUrl(string $locale = 'en'): string
    {
        $slug = $locale === 'ar' ? ($this->seo_slug_ar ?? $this->seo_slug_en ?? $this->slug) : ($this->seo_slug_en ?? $this->slug);
        return "/{$locale}/projects/{$slug}";
    }
}
