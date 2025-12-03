<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    use HasFactory;

    protected $table = 'listings';

    protected $fillable = [
        'unit_id',
        'listing_type',
        'title',
        'title_en',
        'title_ar',
        'slug',
        'slug_en',
        'slug_ar',
        'short_description',
        'status',
        'is_featured',
        'published_at',
        'seo_title',
        'seo_title_en',
        'seo_title_ar',
        'seo_description',
        'seo_description_en',
        'seo_description_ar',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopePrimary($query)
    {
        return $query->where('listing_type', 'primary');
    }

    public function scopeResale($query)
    {
        return $query->where('listing_type', 'resale');
    }

    public function scopeRental($query)
    {
        return $query->where('listing_type', 'rental');
    }

    public function scopeInLocation($query, $cityId = null, $districtId = null)
    {
        return $query->whereHas('unit.project', function($q) use ($cityId, $districtId) {
             if ($districtId) {
                 $q->where('district_id', $districtId);
             } elseif ($cityId) {
                 $q->where('city_id', $cityId);
             }
        });
    }

    public function getFrontendUrl(string $locale = 'en'): string
    {
        $slug = $locale === 'ar' ? ($this->slug_ar ?? $this->slug_en ?? $this->slug) : ($this->slug_en ?? $this->slug);
        return "/{$locale}/listings/{$slug}";
    }
}
