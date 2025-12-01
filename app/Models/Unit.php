<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $table = 'units';

    protected $fillable = [
        'project_id',
        'property_model_id',
        'unit_type_id',
        'unit_number',
        'floor_label',
        'delivery_year',
        'unit_status',
        'bedrooms',
        'bathrooms',
        'finishing_type',
        'orientation',
        'view_label',
        'is_corner',
        'is_furnished',
        'equipment',
        'built_up_area',
        'land_area',
        'garden_area',
        'outdoor_area',
        'roof_area',
        'price',
        'currency_code',
        'price_per_sqm',
        'payment_type',
        'payment_summary',
        'media',
    ];

    protected $casts = [
        'equipment' => 'array',
        'media' => 'array',
        'is_corner' => 'boolean',
        'is_furnished' => 'boolean',
        'delivery_year' => 'integer',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'built_up_area' => 'decimal:2',
        'land_area' => 'decimal:2',
        'garden_area' => 'decimal:2',
        'outdoor_area' => 'decimal:2',
        'roof_area' => 'decimal:2',
        'price' => 'decimal:2',
        'price_per_sqm' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function propertyModel()
    {
        return $this->belongsTo(PropertyModel::class);
    }

    public function unitType()
    {
        return $this->belongsTo(UnitType::class);
    }

    public function listing()
    {
        return $this->hasOne(Listing::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('unit_status', 'available');
    }

    public function scopeByPriceRange($query, $min, $max)
    {
        if ($min !== null) {
            $query->where('price', '>=', $min);
        }
        if ($max !== null) {
            $query->where('price', '<=', $max);
        }
        return $query;
    }
}
