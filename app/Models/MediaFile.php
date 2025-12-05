<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class MediaFile extends Model
{
    use HasFactory;

    protected $table = 'media_files';

    protected $fillable = [
        'disk',
        'path',
        'original_filename',
        'mime_type',
        'extension',
        'size_bytes',
        'width',
        'height',
        'context_type',
        'context_id',
        'country_id',
        'region_id',
        'city_id',
        'district_id',
        'project_id',
        'is_primary',
        'variant_role',
        'variant_of_id',
        'alt_en',
        'alt_ar',
        'title_en',
        'title_ar',
        'seo_keywords_en',
        'seo_keywords_ar',
    ];

    protected $casts = [
        'seo_keywords_en' => 'array',
        'seo_keywords_ar' => 'array',
        'is_primary' => 'boolean',
        'size_bytes' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the URL of the file.
     */
    public function getUrlAttribute()
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Get the parent original file if this is a variant.
     */
    public function originalFile(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'variant_of_id');
    }

    /**
     * Get the variants of this file.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(MediaFile::class, 'variant_of_id');
    }

    // Relationships to Location entities

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
