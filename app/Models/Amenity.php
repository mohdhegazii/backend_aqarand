<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    use HasFactory;

    protected $table = 'amenities';

    protected $fillable = [
        'category_id', // Keeping for backward compatibility or transition, but new logic should use amenity_category_id
        'amenity_category_id',
        'name_en',
        'name_local',
        'slug',
        'icon_class',
        'image_url',
        'image_path',
        'amenity_type',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Deprecated relationship
    public function oldCategory()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function category()
    {
        return $this->belongsTo(AmenityCategory::class, 'amenity_category_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_amenity', 'amenity_id', 'project_id');
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
            return $this->name_local ?? $this->name_en;
        }
        return $this->name_en;
    }

    public function getDisplayNameAttribute(): string
    {
        $locale = app()->getLocale();

        if ($locale === 'ar' && !empty($this->name_local)) {
            return $this->name_local;
        }

        return $this->name_en ?? $this->name_local ?? '';
    }
}
