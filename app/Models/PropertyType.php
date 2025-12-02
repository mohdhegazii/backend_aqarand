<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyType extends Model
{
    use HasFactory;

    protected $table = 'property_types';

    protected $fillable = [
        'name_en',
        'name_local',
        'slug',
        'category',
        'icon_class',
        'image_url',
        'image_path',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public const CATEGORIES = [
        'residential',
        'commercial',
        'administrative',
        'medical',
        'mixed',
        'other'
    ];

    public function unitTypes()
    {
        return $this->hasMany(UnitType::class);
    }

    public function getName($locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        if ($locale === 'ar') {
            return $this->name_local ?? $this->name_en;
        }
        return $this->name_en;
    }
}
