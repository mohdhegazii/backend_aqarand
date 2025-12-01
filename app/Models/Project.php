<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'slug',
        'tagline',
        'description_long',
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
        'brochure_url',
        'hero_image_url',
        'gallery',
        'meta_title',
        'meta_description',
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

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'project_amenity', 'project_id', 'amenity_id');
    }
}
