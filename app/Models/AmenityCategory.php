<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmenityCategory extends Model
{
    use HasFactory;

    protected $table = 'amenity_categories';

    protected $fillable = [
        'name_en',
        'name_ar',
        'slug',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function amenities()
    {
        return $this->hasMany(Amenity::class, 'amenity_category_id');
    }

    /**
     * Get the name based on the locale.
     *
     * @param string|null $locale
     * @return string
     */
    public function getName($locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        if ($locale === 'ar') {
            return $this->name_ar ?? $this->name_en;
        }
        return $this->name_en;
    }

    public function getDisplayNameAttribute(): string
    {
        $locale = app()->getLocale();

        if ($locale === 'ar' && !empty($this->name_ar)) {
            return $this->name_ar;
        }

        return $this->name_en ?? $this->name_ar ?? '';
    }
}
