<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        'logo',
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

        $rawCandidates = [
            'logo_path' => $this->getAttribute('logo_path'),
            'logo' => $this->getAttribute('logo'),
            'logo_url_column' => $this->getRawOriginal('logo_url') ?? ($this->getAttributes()['logo_url'] ?? null),
            'image' => $this->getAttribute('image'),
            'image_path' => $this->getAttribute('image_path'),
        ];

        $raw = null;
        $rawSourceKey = null;

        foreach ($rawCandidates as $key => $value) {
            if (is_string($value) && trim($value) !== '') {
                $raw = trim($value);
                $rawSourceKey = $key;
                break;
            }
        }

        $debug = [
            'developer_id' => $this->id ?? null,
            'raw' => $raw,
            'raw_source_key' => $rawSourceKey,
            'candidate_attributes' => array_filter($rawCandidates, fn($v) => $v !== null && $v !== ''),
            'storage_disk_root' => config('filesystems.disks.public.root') ?? null,
            'public_path' => public_path(),
            'public_storage_symlink_exists' => file_exists(public_path('storage')),
            'public_storage_symlink_target' => realpath(public_path('storage')) ?: null,
            'candidate_urls' => [],
            'exists_storage' => null,
            'exists_public' => null,
        ];

        if (!$raw) {
            \Log::warning('Developer logo missing raw value', $debug);
            return $this->logoResolutionCache = [
                'url' => null,
                'debug' => $debug,
            ];
        }

        if (Str::startsWith($raw, ['http://', 'https://'])) {
            $debug['candidate_urls'][] = $raw;
            \Log::debug('Developer logo using full URL', $debug);

            return $this->logoResolutionCache = [
                'url' => $raw,
                'debug' => $debug,
            ];
        }

        $normalizedRaw = str_replace('\\', '/', $raw);
        $normalizedRaw = Str::startsWith($normalizedRaw, 'storage/') ? substr($normalizedRaw, 8) : $normalizedRaw;

        $storagePath = ltrim($normalizedRaw, '/');
        $debug['candidate_urls'][] = 'storage://' . $storagePath;

        $existsOnStorage = Storage::disk('public')->exists($storagePath);
        $debug['exists_storage'] = $existsOnStorage;

        if ($existsOnStorage) {
            $url = Storage::disk('public')->url($storagePath);
            $debug['candidate_urls'][] = $url;
            \Log::debug('Developer logo resolved via Storage::disk(public)', $debug);

            return $this->logoResolutionCache = [
                'url' => $url,
                'debug' => $debug,
            ];
        }

        $publicStoragePath = public_path('storage/' . ltrim($raw, '/'));
        $debug['candidate_urls'][] = $publicStoragePath;
        $existsOnPublic = file_exists($publicStoragePath);
        $debug['exists_public'] = $existsOnPublic;

        if ($existsOnPublic) {
            $url = asset('storage/' . ltrim($raw, '/'));
            $debug['candidate_urls'][] = $url;
            \Log::debug('Developer logo resolved via public/storage', $debug);

            return $this->logoResolutionCache = [
                'url' => $url,
                'debug' => $debug,
            ];
        }

        \Log::error('Developer logo could NOT be resolved', $debug);

        return $this->logoResolutionCache = [
            'url' => null,
            'debug' => $debug,
        ];
    }
}
