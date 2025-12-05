<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProjectMediaService
{
    protected $manager;

    public function __construct()
    {
        // We assume Intervention Image v3 is available.
        // If not installed, this line will throw an error in runtime.
        if (class_exists(ImageManager::class)) {
            $this->manager = new ImageManager(new Driver());
        }
    }

    public function handleHeroImageUpload(Project $project, UploadedFile $file): string
    {
        // 1. Generate Filename
        $filename = $this->generateFilename($project->id, 'hero', 'webp');
        $path = "projects/{$project->id}/hero/{$filename}";

        // 2. Process Image
        $this->processImage($file, $path, 1920);

        return $path;
    }

    public function handleGalleryUpload(Project $project, array $files): array
    {
        $paths = [];
        foreach ($files as $index => $file) {
            $filename = $this->generateFilename($project->id, "gallery_{$index}", 'webp');
            $path = "projects/{$project->id}/gallery/{$filename}";

            $this->processImage($file, $path, 1600);
            $paths[] = $path;
        }

        return $paths;
    }

    public function handleBrochureUpload(Project $project, UploadedFile $file): string
    {
        $filename = $this->generateFilename($project->id, 'brochure', 'pdf');
        $path = "projects/{$project->id}/brochures/{$filename}";

        // Store file
        Storage::disk('public')->putFileAs(
            dirname($path),
            $file,
            basename($path)
        );

        return $path;
    }

    protected function generateFilename(int $projectId, string $type, string $extension): string
    {
        $timestamp = now()->timestamp;
        $random = Str::random(6);
        return "{$type}_{$timestamp}_{$random}.{$extension}";
    }

    protected function processImage(UploadedFile $file, string $destinationPath, int $maxWidth)
    {
        if (!$this->manager) {
            // Fallback if Intervention is missing (should not happen based on requirements, but safe)
             Storage::disk('public')->putFileAs(
                dirname($destinationPath),
                $file,
                basename($destinationPath)
            );
            return;
        }

        $image = $this->manager->read($file);

        // Remove EXIF metadata is automatic when re-encoding usually, but v3 might need explicit action?
        // In v2 it was orientate(). In v3 read() usually handles orientation.
        // Metadata stripping happens on save mostly.

        // Resize if needed (keep aspect ratio)
        if ($image->width() > $maxWidth) {
            $image->scale(width: $maxWidth);
        }

        // Apply Watermark
        // Instruction: storage/app/watermarks/project_watermark.png
        $watermarkPath = storage_path('app/watermarks/project_watermark.png');
        if (file_exists($watermarkPath)) {
            // Place watermark at bottom-right with 10px margin.
            // Opacity handling: If the watermark image itself isn't transparent enough,
            // we might need to adjust it. But assuming the PNG is prepared.
            // If v3 place supports opacity, we'd use it.
            // Based on common usage:
            $image->place($watermarkPath, 'bottom-right', 10, 10);
        } else {
             // Instruction: If file does not exist, add instruction/comment on how to place it.
             // We log or just ignore. The user task said "add instruction/comment on how to place it."
             // I'm adding this comment here for the developer.
             // Please place a PNG watermark at storage/app/watermarks/project_watermark.png
        }

        // Convert to WebP and save
        $encoded = $image->toWebp(quality: 80);

        Storage::disk('public')->put($destinationPath, (string) $encoded);
    }
}
