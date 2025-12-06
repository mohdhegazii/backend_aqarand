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
        if (class_exists(ImageManager::class)) {
            $this->manager = new ImageManager(new Driver());
        }
    }

    /**
     * Handle Hero Image Upload.
     * Stores in: storage/app/public/projects/{project_id}/hero/
     * Deletes old hero image if exists.
     */
    public function handleHeroImageUpload(Project $project, UploadedFile $file): string
    {
        // Delete old hero if exists
        if ($project->hero_image_url && Storage::disk('public')->exists($project->hero_image_url)) {
            Storage::disk('public')->delete($project->hero_image_url);
        }

        // 1. Generate Filename
        $filename = $this->generateFilename($project->id, 'hero', 'webp');
        $path = "projects/{$project->id}/hero/{$filename}";

        // 2. Process Image
        $this->processImage($file, $path, 1920);

        return $path;
    }

    /**
     * Handle Gallery Images Upload.
     * Stores in: storage/app/public/projects/{project_id}/gallery/
     * Returns array of objects structure for DB, merging with existing if provided (though controller usually handles merge).
     *
     * @param Project $project
     * @param array $files Array of UploadedFile
     * @param array $existingGallery Existing gallery items (optional context)
     * @return array New items only (controller should merge) OR merged items if logic requires.
     *               Standard pattern: Return NEW items. Controller appends.
     */
    public function handleGalleryUpload(Project $project, array $files, array $existingGallery = []): array
    {
        $newItems = [];
        foreach ($files as $index => $file) {
            $filename = $this->generateFilename($project->id, "gallery_" . uniqid(), 'webp');
            $path = "projects/{$project->id}/gallery/{$filename}";

            $this->processImage($file, $path, 1600);

            $newItems[] = [
                'path' => $path,
                'name' => null, // Default
                'alt' => null,  // Default
                'is_hero_candidate' => false,
            ];
        }

        return $newItems;
    }

    /**
     * Handle Brochure PDF Upload.
     * Stores in: storage/app/public/projects/{project_id}/brochures/
     * Document behavior: PDF brochures are stored under storage/app/public/projects/{project_id}/brochures/
     * and referenced via the brochure_url column as a relative path.
     */
    public function handleBrochureUpload(Project $project, UploadedFile $file): string
    {
        // Delete old brochure if exists
        if ($project->brochure_url && Storage::disk('public')->exists($project->brochure_url)) {
            Storage::disk('public')->delete($project->brochure_url);
        }

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
            // Fallback if Intervention is missing
             Storage::disk('public')->putFileAs(
                dirname($destinationPath),
                $file,
                basename($destinationPath)
            );
            return;
        }

        $image = $this->manager->read($file);

        // Resize if needed (keep aspect ratio)
        if ($image->width() > $maxWidth) {
            $image->scale(width: $maxWidth);
        }

        // Apply Watermark
        // Instruction: Please place a PNG watermark at storage/app/watermarks/project_watermark.png
        $watermarkPath = storage_path('app/watermarks/project_watermark.png');
        if (file_exists($watermarkPath)) {
            // Place watermark at bottom-right with 10px margin.
            $image->place($watermarkPath, 'bottom-right', 10, 10);
        }

        // Convert to WebP and save
        $encoded = $image->toWebp(quality: 80);

        Storage::disk('public')->put($destinationPath, (string) $encoded);
    }
}
