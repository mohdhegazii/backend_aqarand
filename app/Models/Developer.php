<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Developer extends Model
{
    use HasFactory;

    protected $table = 'developers';

    protected $fillable = [
        'name',
        'name_en',
        'name_ar',
        'slug',
        'description',
        'description_en',
        'description_ar',
        'logo_path',
        'website',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function seoMeta()
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    // Helper to get bilingual name
    public function getName($locale = null)
    {
        $locale = $locale ?: app()->getLocale();
        if ($locale === 'ar') {
            return $this->name_ar ?? $this->name;
        }
        return $this->name_en ?? $this->name;
    }

    public function getDisplayNameAttribute(): string
    {
        $locale = app()->getLocale();

        if ($locale === 'ar' && !empty($this->name_ar)) {
            return $this->name_ar;
        }

        return $this->name_en ?? $this->name ?? '';
    }

    public function getLogoUrlAttribute()
    {
        $candidates = [
            $this->logo_path,
            $this->logo ?? null,
        ];

        foreach ($candidates as $path) {
            if (!$path) {
                continue;
            }

            if (filter_var($path, FILTER_VALIDATE_URL)) {
                return $path;
            }

            $normalizedPath = ltrim($path, '/');

            if (str_starts_with($normalizedPath, 'storage/')) {
                return asset($normalizedPath);
            }

            if (Storage::disk('public')->exists($normalizedPath)) {
                return Storage::disk('public')->url($normalizedPath);
            }

            if (file_exists(public_path($normalizedPath))) {
                return asset($normalizedPath);
            }
        }

        return null;
    }

    public function getWebsiteUrlAttribute()
    {
        return $this->website;
    }
}
