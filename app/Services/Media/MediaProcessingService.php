<?php

namespace App\Services\Media;

use App\Models\MediaFile;
use App\Models\MediaSetting;
use App\Services\Media\DTO\MediaProcessingResult;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MediaProcessingService
{
    protected MediaDiskResolver $diskResolver;
    protected ?MediaSetting $settings = null;
    protected ImageManager $imageManager;

    public function __construct(MediaDiskResolver $diskResolver)
    {
        $this->diskResolver = $diskResolver;
        // Initialize Intervention Image v3 with GD driver
        $this->imageManager = new ImageManager(new Driver());
    }

    protected function getSettings(): MediaSetting
    {
        if (!$this->settings) {
            $this->settings = MediaSetting::first() ?? new MediaSetting([
                'image_max_width' => 1920,
                'image_quality' => 80,
                'generate_webp' => true,
                'generate_thumb' => true,
                'thumb_width' => 150,
                'medium_width' => 800,
                'large_width' => 1200,
            ]);
        }
        return $this->settings;
    }

    /**
     * Process an image file: resize, optimize, and generate variants.
     *
     * @param string $tmpPath Relative path on media_tmp disk
     * @param MediaFile $media
     * @return MediaProcessingResult
     */
    public function processImage(string $tmpPath, MediaFile $media): MediaProcessingResult
    {
        $result = new MediaProcessingResult();
        $settings = $this->getSettings();

        $disk = Storage::disk($this->diskResolver->getTmpDisk());
        $fullPath = $disk->path($tmpPath);

        // Load image - This is our master instance
        // Note: In v3, read() creates a new instance.
        $masterImage = $this->imageManager->read($fullPath);

        // 1. Process "Original" (Optimized/WebP)
        // We clone or create a fresh instance for the main version to ensure
        // we don't accidentally constrain the master if we were to re-use it (though here we just scale).
        // Actually, for the "original", we want to apply max-width limits.

        $originalWidth = $masterImage->width();

        $targetWidth = $originalWidth;
        if ($settings->image_max_width && $originalWidth > $settings->image_max_width) {
            $targetWidth = $settings->image_max_width;
            $masterImage->scale(width: $targetWidth);
        }

        $quality = $settings->image_quality ?? 80;
        $baseName = pathinfo($tmpPath, PATHINFO_FILENAME);

        // Generate WebP for the main image if enabled
        if ($settings->generate_webp) {
            $encoded = $masterImage->toWebp($quality);
            $ext = 'webp';
        } else {
            // Keep original format but optimized
            $ext = strtolower(pathinfo($media->file_name, PATHINFO_EXTENSION));
            if ($ext === 'jpg' || $ext === 'jpeg') {
                $encoded = $masterImage->toJpeg($quality);
            } elseif ($ext === 'png') {
                $encoded = $masterImage->toPng();
            } else {
                $encoded = $masterImage->toWebp($quality); // Fallback
                $ext = 'webp';
            }
        }

        // Save "original" variant to tmp
        $processedOriginalFilename = $baseName . '_processed.' . $ext;
        $disk->put($processedOriginalFilename, (string) $encoded);

        $result->addVariant(
            'original',
            $processedOriginalFilename,
            $masterImage->width(),
            $masterImage->height(),
            strlen((string) $encoded),
            $ext
        );

        // 2. Generate Thumbnails / Sizes
        $sizes = [
            'thumb' => $settings->thumb_width ?? 150,
            'medium' => $settings->medium_width ?? 800,
            'large' => $settings->large_width ?? 1200,
        ];

        foreach ($sizes as $name => $width) {
            if (!$width) continue;

            // Don't upscale
            if ($width >= $masterImage->width()) continue;

            // Use the already scaled master image as the source for further downscaling.
            // But we must CLONE it to avoid affecting the next iteration (if we were scaling down progressively).
            // Actually, Intervention Image objects are mutable.
            // Since we iterate, if we scale the master, the next iteration gets the smaller one.
            // But here the $sizes loop order is not guaranteed to be descending.
            // SAFEST: Always re-read from source file OR use a fresh clone.
            // Since disk I/O is slow, let's re-read the already-loaded-but-maybe-modified master?
            // Wait, we modified $masterImage above (scaled to max width).
            // That's fine, all thumbs should be <= max width.
            // But we can't reuse $masterImage directly if we scale it further.
            // We need to clone.
            // Intervention v3 doesn't have clone()? It seems it does not.
            // So we re-read the file is the most robust way or re-read the encoded buffer of the "processed original"
            // which is arguably better quality than re-reading a heavily compressed jpeg,
            // but re-reading the *source file* is best for quality.

            $variantImage = $this->imageManager->read($fullPath);
            // Apply max width logic to variant source too?
            // No, just scale directly to target width.

            $variantImage->scale(width: $width);

            // Encode
            if ($settings->generate_webp) {
                $variantEncoded = $variantImage->toWebp($quality);
                $variantExt = 'webp';
            } else {
                // match original extension logic or force jpeg for thumbs?
                // usually thumbs are safe as jpg/webp.
                $variantEncoded = $variantImage->toJpeg($quality);
                $variantExt = 'jpg';
            }

            $variantFilename = $baseName . '_' . $name . '.' . $variantExt;
            $disk->put($variantFilename, (string) $variantEncoded);

            $result->addVariant(
                $name,
                $variantFilename,
                $variantImage->width(),
                $variantImage->height(),
                strlen((string) $variantEncoded),
                $variantExt
            );
        }

        return $result;
    }

    /**
     * Process a PDF file.
     * Currently a placeholder for compression or validitation logic.
     *
     * @param string $tmpPath
     * @param MediaFile $media
     * @return MediaProcessingResult
     */
    public function processPdf(string $tmpPath, MediaFile $media): MediaProcessingResult
    {
        $result = new MediaProcessingResult();
        $disk = Storage::disk($this->diskResolver->getTmpDisk());

        // Ensure file exists
        if (!$disk->exists($tmpPath)) {
            throw new \Exception("File not found at $tmpPath");
        }

        // For now, we just verify it exists and maybe check size.
        // In future: ghostscript compression.

        $size = $disk->size($tmpPath);

        // We treat the raw upload as the "original" variant
        // Since we aren't modifying it yet, we just pass the path through.

        $result->addVariant(
            'original',
            $tmpPath,
            null, // Width n/a
            null, // Height n/a
            $size,
            'pdf'
        );

        return $result;
    }
}
