<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
        'segment_id',
        'name_en',
        'name_ar',
        'slug',
        'image_path',
        'image_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function segment()
    {
        return $this->belongsTo(Segment::class);
    }

    public function amenities()
    {
        return $this->hasMany(Amenity::class);
    }

    public function getName($locale = null)
    {
        $locale = $locale ?: app()->getLocale();
        return $locale === 'ar' ? $this->name_ar : $this->name_en;
    }
}
