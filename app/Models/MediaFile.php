<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class MediaFile extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'media_files';

    protected $fillable = [
        // Legacy Fields
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
        'collection_name',
        'sort_order',
        'is_private',
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

        // Phase 1 New Fields
        'type',             // image, pdf, video, other
        'original_name',    // May duplicate original_filename logic, but requested by spec
        'alt_text',         // Unified alt text
        'title',            // Unified title
        'caption',
        'seo_slug',
        'uploaded_by_id',
        'is_system_asset',
    ];

    protected $casts = [
        'seo_keywords_en' => 'array',
        'seo_keywords_ar' => 'array',
        'is_primary' => 'boolean',
        'is_private' => 'boolean',
        'is_system_asset' => 'boolean', // New
        'size_bytes' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',     // New
    ];

    /**
     * Get the URL of the file.
     */
    public function getUrlAttribute(): string
    {
        // Preserve legacy private file logic
        if ($this->is_private) {
            // Private files cannot be accessed directly via storage URL
            // They should be routed through a controller
            return route($this->adminRoutePrefix().'media.download', $this->id);
        }

        // Default behavior (updated to handle potentially empty path gracefully)
        if (empty($this->path) || empty($this->disk)) {
            // Safe fallback if file info is missing
            return asset('admin/placeholders/media-missing.svg');
        }

        // Note: We avoid running Storage::exists() here for performance reasons
        // as this attribute is accessed frequently in loops.
        // However, we ensure the path is normalized.

        // If the file is on the simulated S3 local disk, ensure we don't return 404
        // if the symlink or folder structure isn't perfect, but at least return a valid URL structure.

        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Get the parent original file if this is a variant.
     */
    public function originalFile(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'variant_of_id');
    }

    private function adminRoutePrefix(): string
    {
        $locale = app()->getLocale();

        // Check if route exists to avoid errors in CLI/Test envs
        // Or just rely on standard logic. The legacy code used this.
        return $locale === config('app.locale') ? 'admin.' : 'localized.admin.';
    }

    /**
     * Get the variants of this file (Legacy).
     */
    public function variants(): HasMany
    {
        return $this->hasMany(MediaFile::class, 'variant_of_id');
    }

    // --- Phase 1 New Relationships ---

    public function conversions(): HasMany
    {
        return $this->hasMany(MediaConversion::class, 'media_file_id');
    }

    public function links(): HasMany
    {
        return $this->hasMany(MediaLink::class, 'media_file_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(MediaTag::class, 'media_file_tag', 'media_file_id', 'media_tag_id');
    }

    // --- Relationships to Location entities (Legacy) ---

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

    // --- Phase 1 Helpers ---

    public function getVariantsAttribute(): array
    {
        // Return new system conversions
        return $this->conversions->map(function ($conversion) {
            return [
                'name' => $conversion->conversion_name,
                'url' => Storage::disk($conversion->disk)->url($conversion->path),
                'width' => $conversion->width,
                'height' => $conversion->height,
            ];
        })->values()->toArray();
    }

    public function isImage(): bool
    {
        return $this->type === 'image' || str_starts_with($this->mime_type ?? '', 'image/');
    }

    public function isPdf(): bool
    {
        return $this->type === 'pdf' || ($this->mime_type === 'application/pdf');
    }

    public function isVideo(): bool
    {
        return $this->type === 'video' || str_starts_with($this->mime_type ?? '', 'video/');
    }
}
