<?php

namespace Tests\Unit\Services\Media;

use App\Models\MediaFile;
use App\Services\Media\MediaPathGenerator;
use Tests\TestCase;

class MediaPathGeneratorTest extends TestCase
{
    public function test_generates_seo_friendly_path_for_projects()
    {
        $generator = new MediaPathGenerator();

        $media = new MediaFile([
            'file_name' => 'test-image.jpg',
            'type' => 'image',
            'seo_slug' => 'beautiful-villa-facade'
        ]);

        // Mock ID since model isn't saved
        $media->id = 123;

        $context = [
            'entity_type' => 'projects',
            'country' => 'Egypt',
            'city' => 'Cairo',
            'slug' => 'grand-residences'
        ];

        $path = $generator->originalPath($media, $context);

        // Expected: projects/egypt/cairo/grand-residences/images/beautiful-villa-facade.jpg
        $this->assertEquals(
            'projects/egypt/cairo/grand-residences/images/beautiful-villa-facade.jpg',
            $path
        );
    }

    public function test_generates_path_with_conversion_suffix()
    {
        $generator = new MediaPathGenerator();

        $media = new MediaFile([
            'file_name' => 'test-image.jpg',
            'type' => 'image',
            'seo_slug' => 'beautiful-villa-facade'
        ]);

        $context = [
            'entity_type' => 'projects',
            'slug' => 'project-x'
        ];

        // For conversion, we assume webp if image
        $path = $generator->conversionPath($media, 'thumb', $context);

        $this->assertEquals(
            'projects/project-x/images/beautiful-villa-facade-thumb.webp',
            $path
        );
    }

    public function test_fallback_path_without_context()
    {
        $generator = new MediaPathGenerator();

        $media = new MediaFile([
            'file_name' => 'random.pdf',
            'type' => 'pdf',
            'seo_slug' => null // No SEO slug
        ]);
        $media->id = 999;

        $path = $generator->originalPath($media, []);

        // Expected: uploads/pdf/Y/m/random.pdf
        $date = date('Y/m');
        $this->assertEquals(
            "uploads/pdf/{$date}/random.pdf",
            $path
        );
    }
}
