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
        'slug',
        'short_description',
        'status',
        'is_featured',
        'published_at',
        'seo_title',
        'seo_description',
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
}
