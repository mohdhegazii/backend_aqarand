<?php

namespace App\Services\Media;

use App\Models\MediaConversion;
use App\Models\MediaFile;
use App\Services\Media\DTO\MediaProcessingResult;
use Illuminate\Support\Facades\Storage;

class MediaStorageService
{
    protected MediaDiskResolver $diskResolver;
    protected MediaPathGenerator $pathGenerator;

    public function __construct(
        MediaDiskResolver $diskResolver,
        MediaPathGenerator $pathGenerator
    ) {
        $this->diskResolver = $diskResolver;
        $this->pathGenerator = $pathGenerator;
    }

    /**
     * Persist processed media variants to the final disk and update the database.
     *
     * @param MediaFile $media
     * @param MediaProcessingResult $result
     * @param array $context
     * @return void
     */
    public function persistProcessedMedia(MediaFile $media, MediaProcessingResult $result, array $context = []): void
    {
        $finalDiskName = $this->diskResolver->getDefaultMediaDisk();
        $finalDisk = Storage::disk($finalDiskName);

        $tmpDiskName = $this->diskResolver->getTmpDisk();
        $tmpDisk = Storage::disk($tmpDiskName);

        foreach ($result->variants as $variant) {
            $conversionName = $variant['conversion_name'];
            $tmpPath = $variant['tmp_path'];

            // Generate final path
            // If it's 'original', we use originalPath logic, but we might want to respect
            // the extension change (e.g., jpg -> webp).
            // The pathGenerator normally takes the MediaFile state.
            // But here we might have a new extension.

            // We need to hint the path generator about the extension change if any.
            // Currently pathGenerator derives extension from MediaFile name or assumes defaults.
            // Let's rely on pathGenerator's conversionPath logic for everything
            // OR use originalPath for 'original' conversion but update the extension.

            if ($conversionName === 'original') {
                $finalPath = $this->pathGenerator->originalPath($media, $context);
                // Fix extension if changed (e.g. image -> webp)
                if (isset($variant['extension'])) {
                    $finalPath = $this->replaceExtension($finalPath, $variant['extension']);
                }
            } else {
                $finalPath = $this->pathGenerator->conversionPath($media, $conversionName, $context);
                // Fix extension
                if (isset($variant['extension'])) {
                    $finalPath = $this->replaceExtension($finalPath, $variant['extension']);
                }
            }

            // Move file (Copy then delete, to be safe across disks)
            $stream = $tmpDisk->readStream($tmpPath);
            $finalDisk->put($finalPath, $stream); // visibility default from config usually
            if (is_resource($stream)) {
                fclose($stream);
            }

            // Update DB
            if ($conversionName === 'original') {
                // Update main MediaFile record
                $media->update([
                    'disk' => $finalDiskName,
                    'path' => $finalPath,
                    // If we changed format, we might want to update file_name extension too?
                    // For now, let's keep file_name as the user uploaded name for reference,
                    // but the path points to the actual file.
                    // Ideally, mime_type should be updated if changed.
                    'mime_type' => $this->guessMimeType($variant['extension']),
                    'size_bytes' => $variant['size_bytes'],
                    // 'width' / 'height' columns don't exist on MediaFile in standard schema?
                    // Memory says Phase 1 created media_files table.
                    // Let's check schema if needed. But assuming standard fields.
                ]);

                // If media_files has width/height columns (from Phase 1? Memory doesn't explicitly list them for media_files, only conversions)
                // If they exist, update them. Else ignore.
            }

            // Always create/update a conversion record for every variant?
            // Usually 'original' is just the MediaFile.
            // But if we want uniformity, we can have a conversion record for 'original' too.
            // Phase 1 requirements were minimal.
            // Let's store actual conversions (thumbs, etc) in media_conversions.

            if ($conversionName !== 'original') {
                MediaConversion::updateOrCreate(
                    [
                        'media_file_id' => $media->id,
                        'conversion_name' => $conversionName,
                    ],
                    [
                        'disk' => $finalDiskName,
                        'path' => $finalPath,
                        'width' => $variant['width'],
                        'height' => $variant['height'],
                        'size_bytes' => $variant['size_bytes'],
                    ]
                );
            }

            // Clean up tmp
            $tmpDisk->delete($tmpPath);
        }
    }

    protected function replaceExtension(string $path, string $newExt): string
    {
        $info = pathinfo($path);
        return ($info['dirname'] === '.' ? '' : $info['dirname'] . '/') . $info['filename'] . '.' . $newExt;
    }

    protected function guessMimeType(string $ext): string
    {
        return match (strtolower($ext)) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            default => 'application/octet-stream',
        };
    }
}
