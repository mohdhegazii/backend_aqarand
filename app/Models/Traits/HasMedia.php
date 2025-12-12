<?php

namespace App\Models\Traits;

use App\Models\MediaFile;
use App\Models\MediaLink;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasMedia
{
    /**
     * Get all media links for this model.
     */
    public function mediaLinks(): MorphMany
    {
        return $this->morphMany(MediaLink::class, 'model');
    }

    /**
     * Get the featured media file (single).
     *
     * @return MediaFile|null
     */
    public function featuredMedia(): ?MediaFile
    {
        $link = $this->mediaLinks()
            ->where('role', 'featured')
            ->orderBy('ordering')
            ->with('mediaFile')
            ->first();

        return $link ? $link->mediaFile : null;
    }

    /**
     * Get gallery media files (collection).
     *
     * @return Collection<int, MediaFile>
     */
    public function galleryMedia(): Collection
    {
        return $this->mediaLinks()
            ->where('role', 'gallery')
            ->orderBy('ordering')
            ->with('mediaFile')
            ->get()
            ->pluck('mediaFile')
            ->filter();
    }

    /**
     * Attach a media file to this model.
     *
     * @param int|MediaFile $media
     * @param string $role
     * @param int $ordering
     * @return MediaLink
     */
    public function attachMedia($media, string $role = 'gallery', int $ordering = 0): MediaLink
    {
        $mediaId = $media instanceof MediaFile ? $media->id : $media;

        return $this->mediaLinks()->create([
            'media_file_id' => $mediaId,
            'role' => $role,
            'ordering' => $ordering,
        ]);
    }

    /**
     * Detach media files with a specific role.
     *
     * @param string $role
     * @return void
     */
    public function detachMedia(string $role): void
    {
        $this->mediaLinks()->where('role', $role)->delete();
    }

    /**
     * Sync media files for a specific role.
     * Detaches existing media for the role and attaches new ones in the given order.
     *
     * @param array|int|MediaFile $media
     * @param string $role
     * @return void
     */
    public function syncMedia($media, string $role = 'gallery'): void
    {
        $this->detachMedia($role);

        $mediaIds = is_array($media) ? $media : [$media];

        foreach ($mediaIds as $index => $mediaId) {
            if ($mediaId) {
                $this->attachMedia($mediaId, $role, $index);
            }
        }
    }
}
