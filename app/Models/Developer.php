<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Developer extends Model
{
    use HasFactory;

    protected array $logoResolutionCache = [];

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
        return $this->resolveLogo()['url'] ?? null;
    }

    public function getLogoDebugAttribute(): array
    {
        return $this->resolveLogo()['debug'] ?? [];
    }

    public function getWebsiteUrlAttribute()
    {
        return $this->website;
    }

    protected function resolveLogo(): array
    {
        if (!empty($this->logoResolutionCache)) {
            return $this->logoResolutionCache;
        }

        $debug = [];
        $candidates = [
            $this->logo_path,
            $this->logo ?? null,
        ];

        foreach ($candidates as $path) {
            if (!$path) {
                $debug[] = 'Empty path candidate skipped';
                continue;
            }

            $normalizedPath = ltrim($path, '/');

            if (filter_var($normalizedPath, FILTER_VALIDATE_URL)) {
                return $this->logoResolutionCache = [
                    'url' => $normalizedPath,
                    'debug' => $debug,
                    'raw' => $path,
                ];
            }

            if (str_starts_with($normalizedPath, 'storage/')) {
                if (file_exists(public_path($normalizedPath))) {
                    return $this->logoResolutionCache = [
                        'url' => asset($normalizedPath),
                        'debug' => $debug,
                        'raw' => $path,
                    ];
                }

                $debug[] = "Path under public storage missing: {$normalizedPath}";
            }

            if (Storage::disk('public')->exists($normalizedPath)) {
                return $this->logoResolutionCache = [
                    'url' => Storage::disk('public')->url($normalizedPath),
                    'debug' => $debug,
                    'raw' => $path,
                ];
            }

            $debug[] = "File not found on public disk: {$normalizedPath}";

            if (file_exists(public_path($normalizedPath))) {
                return $this->logoResolutionCache = [
                    'url' => asset($normalizedPath),
                    'debug' => $debug,
                    'raw' => $path,
                ];
            }

            $debug[] = "File not found in public path: {$normalizedPath}";
        }

        $this->logoResolutionCache = [
            'url' => null,
            'debug' => $debug,
            'raw' => $this->logo_path ?? $this->logo ?? null,
        ];

        if (config('app.debug') && ($this->logo_path || $this->logo)) {
            Log::warning('Developer logo missing or invalid', [
                'developer_id' => $this->id,
                'raw_logo_path' => $this->logo_path,
                'raw_logo_legacy' => $this->logo,
                'debug' => $debug,
            ]);
        }

        return $this->logoResolutionCache;
    }
}
