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
            $debug[] = "Evaluating raw logo path: {$path}";

            if (filter_var($normalizedPath, FILTER_VALIDATE_URL)) {
                $debug[] = 'Path detected as full URL';
                return $this->logoResolutionCache = [
                    'url' => $normalizedPath,
                    'debug' => $debug,
                    'raw' => $path,
                ];
            }

            $diskCandidate = $normalizedPath;
            $publicStoragePrefixed = str_starts_with($normalizedPath, 'storage/')
                ? substr($normalizedPath, strlen('storage/'))
                : $normalizedPath;

            if ($diskCandidate !== $publicStoragePrefixed) {
                $debug[] = "Normalized disk candidate from storage/ prefix: {$publicStoragePrefixed}";
                $diskCandidate = $publicStoragePrefixed;
            }

            if (Storage::disk('public')->exists($diskCandidate)) {
                $storageUrl = Storage::disk('public')->url($diskCandidate);
                $assetUrl = asset('storage/' . $diskCandidate);
                $storagePath = storage_path('app/public/' . $diskCandidate);
                $publicStoragePath = public_path('storage/' . $diskCandidate);
                $publicStorageExists = file_exists($publicStoragePath);

                if (!$publicStorageExists && file_exists($storagePath)) {
                    $debug[] = "File exists in storage/app/public but not in public/storage (missing storage:link?)";
                }

                $debug[] = "Found on public disk as {$diskCandidate}";
                $debug[] = "Resolved storage URL={$storageUrl}";
                $debug[] = "Resolved asset URL={$assetUrl}";

                // Inline fallback when the storage symlink is missing so the image can still render
                $inlineUrl = null;
                if (!$publicStorageExists) {
                    try {
                        $size = Storage::disk('public')->size($diskCandidate);
                        if ($size <= 2 * 1024 * 1024) {
                            $mimeType = Storage::disk('public')->mimeType($diskCandidate) ?? 'image/png';
                            $contents = Storage::disk('public')->get($diskCandidate);
                            $inlineUrl = 'data:' . $mimeType . ';base64,' . base64_encode($contents);
                            $debug[] = "Generated inline data URI fallback (size={$size})";
                        } else {
                            $debug[] = "Skipped inline data URI fallback because file is larger than 2MB (size={$size})";
                        }
                    } catch (\Throwable $e) {
                        $debug[] = 'Inline data URI fallback failed: ' . $e->getMessage();
                    }
                }

                return $this->logoResolutionCache = [
                    'url' => $inlineUrl ?: $assetUrl,
                    'debug' => $debug,
                    'raw' => $path,
                ];
            }
            $debug[] = "File not found on public disk: {$diskCandidate}";

            $publicPathCandidate = public_path($normalizedPath);
            if (file_exists($publicPathCandidate)) {
                $debug[] = "Found in public path: {$publicPathCandidate}";
                return $this->logoResolutionCache = [
                    'url' => asset($normalizedPath),
                    'debug' => $debug,
                    'raw' => $path,
                ];
            }
            $debug[] = "File not found in public path: {$publicPathCandidate}";

            $publicPathWithStorage = public_path('storage/' . $publicStoragePrefixed);
            if (file_exists($publicPathWithStorage)) {
                $debug[] = "Found in public/storage: {$publicPathWithStorage}";
                return $this->logoResolutionCache = [
                    'url' => asset('storage/' . $publicStoragePrefixed),
                    'debug' => $debug,
                    'raw' => $path,
                ];
            }

            $debug[] = "File not found in public/storage: {$publicPathWithStorage}";

            $fallbackUrl = asset('storage/' . $publicStoragePrefixed);
            $debug[] = "Using fallback URL guess: {$fallbackUrl}";

            return $this->logoResolutionCache = [
                'url' => $fallbackUrl,
                'debug' => $debug,
                'raw' => $path,
            ];
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
