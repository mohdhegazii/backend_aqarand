<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Developer extends Model
{
    use HasFactory;

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saved(function ($developer) {
            Cache::forget('developers.active.list');
        });

        static::deleted(function ($developer) {
            Cache::forget('developers.active.list');
        });
    }

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
        'logo_url',
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

    /**
     * Resolve the logo URL with debug information.
     * Checks multiple columns and storage locations.
     */
    protected function resolveLogo(): array
    {
        if (!empty($this->logoResolutionCache)) {
            return $this->logoResolutionCache;
        }

        // Check all possible columns
        $raw = $this->logo_path ?? $this->logo ?? $this->logo_url ?? null;

        $debug = [
            'developer_id' => $this->id ?? null,
            'raw' => $raw,
            'disk_root' => config('filesystems.disks.public.root') ?? null,
            'public_path' => public_path(),
            'exists_on_disk' => null,
            'exists_in_public_storage' => null,
            'candidate_public_url' => null,
            'public_storage_path' => null,
            'fallback_url' => null,
        ];

        if (!$raw) {
            // No logo set
            if (config('app.debug')) {
                \Log::debug('Developer logo: empty raw value', $debug);
            }
            return $this->logoResolutionCache = [
                'url' => null,
                'debug' => $debug,
            ];
        }

        // If full URL
        if (Str::startsWith($raw, ['http://', 'https://'])) {
            $debug['candidate_public_url'] = $raw;
            if (config('app.debug')) {
                \Log::debug('Developer logo: using full URL', $debug);
            }
            return $this->logoResolutionCache = [
                'url' => $raw,
                'debug' => $debug,
            ];
        }

        // Ensure we don't double slash if raw starts with /
        $storagePath = ltrim($raw, '/');

        // Check under public/storage (physical file check)
        // This handles cases where symlink might be involved or direct file placement
        $publicStoragePath = public_path('storage/' . $storagePath);
        $existsPublic = file_exists($publicStoragePath);
        $debug['exists_in_public_storage'] = $existsPublic;
        $debug['public_storage_path'] = $publicStoragePath;

        if ($existsPublic) {
            // Force relative URL
            $url = '/storage/' . $storagePath;

            $debug['candidate_public_url'] = $url;
            if (config('app.debug')) {
                \Log::debug('Developer logo: resolved via public/storage path', $debug);
            }
            return $this->logoResolutionCache = [
                'url' => $url,
                'debug' => $debug,
            ];
        }

        // Check on storage disk "public" and use fallback route when needed
        $existsOnDisk = Storage::disk('public')->exists($storagePath);
        $debug['exists_on_disk'] = $existsOnDisk;

        if ($existsOnDisk) {
            // Force relative URL for local storage via controller to avoid server path conflicts
            $url = route('media.fallback', ['path' => $storagePath], false);

            $debug['candidate_public_url'] = $url;
            $debug['fallback_url'] = $url;
            if (config('app.debug')) {
                \Log::debug('Developer logo: resolved via media fallback route', $debug);
            }
            return $this->logoResolutionCache = [
                'url' => $url,
                'debug' => $debug,
            ];
        }

        // Could not resolve
        if (config('app.debug')) {
            \Log::error('Developer logo: could NOT resolve path', $debug);
        }

        return $this->logoResolutionCache = [
            'url' => null,
            'debug' => $debug,
        ];
    }
}
