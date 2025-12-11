<?php

namespace App\Services\Media;

use App\Models\MediaFile;
use Illuminate\Support\Str;

class MediaPathGenerator
{
    /**
     * Generate the path for the original media file.
     *
     * @param MediaFile $media
     * @param array $context Contextual data (e.g., entity info, country, slug)
     * @return string
     */
    public function originalPath(MediaFile $media, array $context = []): string
    {
        $basePath = $this->getBasePath($media, $context);
        $filename = $this->getFilename($media, null);

        return $basePath . '/' . $filename;
    }

    /**
     * Generate the path for a specific conversion.
     *
     * @param MediaFile $media
     * @param string $conversionName
     * @param array $context
     * @return string
     */
    public function conversionPath(MediaFile $media, string $conversionName, array $context = []): string
    {
        $basePath = $this->getBasePath($media, $context);
        $filename = $this->getFilename($media, $conversionName);

        return $basePath . '/' . $filename;
    }

    /**
     * Determine the base directory path based on context and file type.
     *
     * @param MediaFile $media
     * @param array $context
     * @return string
     */
    protected function getBasePath(MediaFile $media, array $context): string
    {
        // 1. Determine type-based root
        $typeRoot = match ($media->type) {
            'image' => 'images',
            'pdf' => 'pdf',
            'video' => 'video',
            default => 'misc',
        };

        // 2. Build entity path from context if available
        // Expected context keys: 'entity_type', 'country', 'region', 'city', 'slug'
        // Example structure: projects/egypt/cairo/new-cairo/my-project-slug
        if (!empty($context['entity_type'])) {
            $parts = [$context['entity_type']]; // e.g., 'projects'

            // Add location hierarchy if provided
            if (!empty($context['country'])) $parts[] = Str::slug($context['country']);
            if (!empty($context['region'])) $parts[] = Str::slug($context['region']);
            if (!empty($context['city'])) $parts[] = Str::slug($context['city']);

            // Add entity slug/identifier
            if (!empty($context['slug'])) {
                $parts[] = Str::slug($context['slug']);
            } elseif (!empty($context['id'])) {
                $parts[] = (string) $context['id'];
            }

            return implode('/', $parts) . '/' . $typeRoot;
        }

        // Fallback: generic date-based or type-based folder
        return 'uploads/' . $typeRoot . '/' . date('Y/m');
    }

    /**
     * Generate the filename using seo_slug and conversion suffix.
     *
     * @param MediaFile $media
     * @param string|null $conversionName
     * @return string
     */
    protected function getFilename(MediaFile $media, ?string $conversionName): string
    {
        // Use seo_slug if available, otherwise sanitize original name or use ID
        $baseName = $media->seo_slug
            ? Str::slug($media->seo_slug)
            : pathinfo($media->file_name, PATHINFO_FILENAME);

        // Ensure we have something
        if (empty($baseName)) {
            $baseName = 'file-' . $media->id;
        }

        // Add conversion suffix if present
        if ($conversionName) {
            $baseName .= '-' . $conversionName;
        }

        // Determine extension
        // If conversion is typically webp, we might want to enforce that,
        // but for now, let's respect the media file extension or allow override logic.
        // Simple logic: if conversion is webp-related, use webp, else use original extension.
        // For this phase, we'll stick to original extension unless specifically handling format changes elsewhere.
        // However, usually conversion path generation implies we know the target format.
        // Let's assume webp for images if conversionName is present, as per task description example.

        $extension = pathinfo($media->file_name, PATHINFO_EXTENSION);

        // If we are doing image conversions, we likely want .webp
        if ($media->type === 'image' && $conversionName) {
             // If the system is configured to generate webp, we use webp.
             // But strictly speaking, the caller might want a jpg thumb.
             // For now, let's default to .webp for conversions as per the prompt example:
             // "compound-x-main-image-thumb.webp"
             $extension = 'webp';
        }

        return $baseName . '.' . $extension;
    }
}
