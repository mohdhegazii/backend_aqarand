<?php

namespace App\Services\Media;

use App\Models\MediaFile;
use App\Models\Project;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MediaProcessor
{
    protected $disk = 'public';
    protected $manager;
    protected $altGenerator;

    public function __construct(AltTextGenerator $altGenerator)
    {
        // Using Intervention Image v3 syntax
        $this->manager = new ImageManager(new Driver());
        $this->altGenerator = $altGenerator;
    }

    /**
     * Store and process a project image.
     */
    public function storeProjectImage(Project $project, UploadedFile $file, array $seoData = [], bool $isPrimary = false)
    {
        // 1. Determine Path
        $countryCode = $project->country ? $project->country->code : 'xx';
        $regionSlug = $project->region ? $project->region->slug : 'general';
        $citySlug = $project->city ? $project->city->slug : 'general';
        $districtSlug = $project->district ? $project->district->slug : 'none';
        $projectSlug = $project->seo_slug_en ?? $project->slug ?? 'project-' . $project->id;

        $basePath = "media/projects/{$countryCode}/{$regionSlug}/{$citySlug}/{$districtSlug}/{$projectSlug}";

        $filenameBase = $this->generateFilename($project->seo_slug_en, $project->seo_slug_ar);

        return $this->processAndSave($file, $basePath, $filenameBase, 'project', $project->id, [
            'country_id' => $project->country_id,
            'region_id' => $project->region_id,
            'city_id' => $project->city_id,
            'district_id' => $project->district_id,
            'project_id' => $project->id,
            'is_primary' => $isPrimary,
        ], $project);
    }

    /**
     * Store and process a unit image.
     */
    public function storeUnitImage($unit, UploadedFile $file, array $seoData = [])
    {
        $project = $unit->project;

        $countryCode = $project && $project->country ? $project->country->code : 'xx';
        $regionSlug = $project && $project->region ? $project->region->slug : 'general';
        $citySlug = $project && $project->city ? $project->city->slug : 'general';
        $districtSlug = $project && $project->district ? $project->district->slug : 'none';
        $projectSlug = $project ? ($project->seo_slug_en ?? $project->slug) : 'none';

        $basePath = "media/units/{$countryCode}/{$regionSlug}/{$citySlug}/{$districtSlug}/{$projectSlug}/{$unit->id}";

        $filenameBase = $project
            ? $this->generateFilename($project->seo_slug_en, $project->seo_slug_ar, $unit->id)
            : "unit-{$unit->id}-" . Str::random(6);

        $locationData = [
            'country_id' => $project ? $project->country_id : null,
            'region_id' => $project ? $project->region_id : null,
            'city_id' => $project ? $project->city_id : null,
            'district_id' => $project ? $project->district_id : null,
            'project_id' => $project ? $project->id : null,
            'context_id' => $unit->id,
            'context_type' => 'unit',
        ];

        return $this->processAndSave($file, $basePath, $filenameBase, 'unit', $unit->id, $locationData, $project);
    }

    /**
     * Store and process a blog image.
     */
    public function storeBlogImage($blogPost, UploadedFile $file)
    {
        $year = now()->format('Y');
        $month = now()->format('m');
        $slug = $blogPost->slug_en ?? $blogPost->id;

        $basePath = "media/blog/{$year}/{$month}/{$slug}";
        $filenameBase = ($blogPost->slug_en ?? 'blog-post') . '-' . Str::random(6);

        return $this->processAndSave($file, $basePath, $filenameBase, 'blog', $blogPost->id, [
            'context_type' => 'blog',
            'context_id' => $blogPost->id,
        ], null, $blogPost);
    }

    /**
     * Core processing logic.
     */
    protected function processAndSave(UploadedFile $file, string $basePath, string $filenameBase, string $contextType, int $contextId, array $metaData = [], $projectContext = null, $blogContext = null)
    {
        try {
            $altTexts = $this->generateAltTexts($projectContext, $blogContext);

            $image = $this->manager->read($file);

            // WEBP (Primary)
            $webpFilename = $filenameBase . '.webp';
            $webpPath = $basePath . '/' . $webpFilename;
            $encodedWebp = $image->toWebp(80);
            Storage::disk($this->disk)->put($webpPath, (string) $encodedWebp);

            $mediaFileWebp = MediaFile::create(array_merge($metaData, [
                'disk' => $this->disk,
                'path' => $webpPath,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => 'image/webp',
                'extension' => 'webp',
                'size_bytes' => strlen((string) $encodedWebp),
                'width' => $image->width(),
                'height' => $image->height(),
                'variant_role' => 'webp',
                'alt_en' => $altTexts['en'],
                'alt_ar' => $altTexts['ar'],
                'seo_keywords_en' => $altTexts['keywords_en'] ?? null,
                'seo_keywords_ar' => $altTexts['keywords_ar'] ?? null,
            ]));

            // Watermarked Variant
            if ($contextType === 'project' || $contextType === 'unit') {
                 // Create fresh instance
                 $watermarkedImage = $this->manager->read($file);

                 $watermarkPath = storage_path('app/media/watermark.png');
                 if (!file_exists($watermarkPath) && file_exists(public_path('watermark.png'))) {
                     $watermarkPath = public_path('watermark.png');
                 }

                 if (file_exists($watermarkPath)) {
                     // Intervention Image v3 place() signature
                     $watermarkedImage->place(
                         $watermarkPath,
                         'bottom-right',
                         10,
                         10,
                         50
                     );

                     $wmFilename = $filenameBase . '-wm.webp';
                     $wmPath = $basePath . '/' . $wmFilename;
                     $encodedWm = $watermarkedImage->toWebp(80);
                     Storage::disk($this->disk)->put($wmPath, (string) $encodedWm);

                     MediaFile::create(array_merge($metaData, [
                        'disk' => $this->disk,
                        'path' => $wmPath,
                        'original_filename' => $file->getClientOriginalName(),
                        'mime_type' => 'image/webp',
                        'extension' => 'webp',
                        'size_bytes' => strlen((string) $encodedWm),
                        'width' => $image->width(),
                        'height' => $image->height(),
                        'variant_role' => 'watermarked',
                        'variant_of_id' => $mediaFileWebp->id,
                        'alt_en' => $altTexts['en'],
                        'alt_ar' => $altTexts['ar'],
                    ]));
                 }
            }

            // AVIF
            try {
                $avifFilename = $filenameBase . '.avif';
                $avifPath = $basePath . '/' . $avifFilename;
                $encodedAvif = $image->toAvif(80);
                Storage::disk($this->disk)->put($avifPath, (string) $encodedAvif);

                MediaFile::create(array_merge($metaData, [
                    'disk' => $this->disk,
                    'path' => $avifPath,
                    'original_filename' => $file->getClientOriginalName(),
                    'mime_type' => 'image/avif',
                    'extension' => 'avif',
                    'size_bytes' => strlen((string) $encodedAvif),
                    'width' => $image->width(),
                    'height' => $image->height(),
                    'variant_role' => 'avif',
                    'variant_of_id' => $mediaFileWebp->id,
                    'alt_en' => $altTexts['en'],
                    'alt_ar' => $altTexts['ar'],
                ]));
            } catch (\Exception $e) {
                // AVIF might not be supported
            }

            return $mediaFileWebp;

        } catch (\Exception $e) {
            // Log error
            Log::error("Media Processing Failed: " . $e->getMessage(), [
                'file' => $file->getClientOriginalName(),
                'context' => $contextType,
                'id' => $contextId
            ]);

            // Save original for debugging
            try {
                $debugPath = "debug/" . date('Y-m-d') . "/" . Str::random(10) . "-" . $file->getClientOriginalName();
                Storage::disk($this->disk)->putFileAs(dirname($debugPath), $file, basename($debugPath));
                Log::info("Saved failed upload for debug at: " . $debugPath);
            } catch (\Exception $storeError) {
                Log::error("Could not save debug file: " . $storeError->getMessage());
            }

            throw $e;
        }
    }

    protected function generateFilename($slugEn, $slugAr, $extra = null)
    {
        $sEn = $slugEn ? Str::slug($slugEn) : 'img';
        $sAr = $slugAr ? preg_replace('/[^\p{L}\p{N}\-_]/u', '-', $slugAr) : 'ar';
        $random = Str::random(4);

        $name = "{$sEn}-{$sAr}";
        if ($extra) {
            $name .= "-{$extra}";
        }
        $name .= "-{$random}";

        return trim(preg_replace('/-+/', '-', $name), '-');
    }

    protected function generateAltTexts($project = null, $blogPost = null)
    {
        if ($project) {
            return $this->altGenerator->generateForProject($project);
        }
        if ($blogPost) {
            return $this->altGenerator->generateForBlog($blogPost);
        }
        return ['en' => '', 'ar' => ''];
    }
}
