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
        'code',
        'description',
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
        'meta_title',
        'meta_description',
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
}
