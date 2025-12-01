<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    use HasFactory;

    protected $table = 'amenities';

    protected $fillable = [
        'name_en',
        'name_local',
        'slug',
        'icon_class',
        'amenity_type',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

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
}
