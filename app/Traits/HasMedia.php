<?php

namespace App\Traits;

use App\Models\MediaFile;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasMedia
{
    /**
     * Get all media files for this entity.
     */
    public function mediaFiles(): HasMany
    {
        // Manual "polymorphic" relationship using context columns
        // Assuming context_type matches the model's morph key (e.g. 'project')
        return $this->hasMany(MediaFile::class, 'context_id')
                    ->where('context_type', $this->getMediaType());
    }

    /**
     * Get media by collection.
     */
    public function getMedia(string $collection = 'default')
    {
        return $this->mediaFiles()
                    ->where('collection_name', $collection)
                    ->orderBy('sort_order')
                    ->get();
    }

    /**
     * Get the first image URL (Hero/Thumbnail).
     */
    public function getFirstMediaUrl(string $collection = 'default'): ?string
    {
        $media = $this->mediaFiles()
                      ->where('collection_name', $collection)
                      ->orderBy('is_primary', 'desc')
                      ->orderBy('sort_order')
                      ->first();

        return $media ? $media->url : null;
    }

    /**
     * Define the context type string for the model (e.g., 'project', 'unit').
     * Can be overridden in the model.
     */
    public function getMediaType(): string
    {
        return strtolower(class_basename($this));
    }
}
