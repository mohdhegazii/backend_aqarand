<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitType extends Model
{
    use HasFactory;

    protected $table = 'unit_types';

    protected $fillable = [
        'property_type_id',
        'name',
        'code',
        'description',
        'icon_class',
        'image_url',
        'is_core_type',
        'requires_land_area',
        'requires_built_up_area',
        'requires_garden_area',
        'requires_roof_area',
        'requires_indoor_area',
        'requires_outdoor_area',
        'additional_rules',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_core_type' => 'boolean',
        'requires_land_area' => 'boolean',
        'requires_built_up_area' => 'boolean',
        'requires_garden_area' => 'boolean',
        'requires_roof_area' => 'boolean',
        'requires_indoor_area' => 'boolean',
        'requires_outdoor_area' => 'boolean',
        'additional_rules' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function propertyType()
    {
        return $this->belongsTo(PropertyType::class);
    }

    public function propertyModels()
    {
        return $this->hasMany(PropertyModel::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }
}
