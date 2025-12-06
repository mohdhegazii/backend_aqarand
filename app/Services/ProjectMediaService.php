<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProjectMediaService
{
    protected $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    public function handleHeroImage(Project $project, UploadedFile $file)
    {
        $path = $file->store("projects/{$project->id}/hero", 'public');

        // Optimize or resize if needed
        // $image = $this->manager->read(storage_path('app/public/' . $path));
        // $image->scale(width: 1920);
        // $image->save();

        return $path;
    }

    public function handleBrochure(Project $project, UploadedFile $file)
    {
        return $file->store("projects/{$project->id}/brochures", 'public');
    }

    public function handleGallery(Project $project, array $files)
    {
        $gallery = $project->gallery ?? [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $path = $file->store("projects/{$project->id}/gallery", 'public');
                $gallery[] = [
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'alt' => $project->name_en, // Default alt
                    'is_hero_candidate' => false
                ];
            }
        }

        return $gallery;
    }
}
