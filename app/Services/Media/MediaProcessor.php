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
     * Unified upload processor for any entity and any file type.
     */
    public function processUpload(UploadedFile $file, $model, string $collection = 'default', array $customMeta = [])
    {
        // 1. Determine file type
        $mime = $file->getMimeType() ?? $file->getClientMimeType();
        $isImage = str_starts_with($mime, 'image/');
        $isPrivate = ($collection === 'contracts' || $collection === 'private' || ($customMeta['is_private'] ?? false));

        // 2. Setup Context
        $contextType = method_exists($model, 'getMediaType') ? $model->getMediaType() : strtolower(class_basename($model));
        $contextId = $model->id;

        // 3. Location Data (Try to extract from model if available)
        $locationData = $this->extractLocationData($model);

        // 4. Generate Filename & Path
        $filenameBase = $this->generateFilename($file, $model, $collection);
        $disk = $isPrivate ? 'local' : 'public';
        $rootPath = $isPrivate ? 'private' : 'media';

        $dirPath = $this->buildDirectoryPath($rootPath, $contextType, $contextId, $collection, $locationData, $model);

        $metaData = array_merge($locationData, [
            'context_type' => $contextType,
            'context_id' => $contextId,
            'collection_name' => $collection,
            'is_private' => $isPrivate,
            'disk' => $disk
        ], $customMeta);

        if ($isImage) {
            return $this->processImage($file, $filenameBase, $dirPath, $metaData, $model);
        } else {
            return $this->processDocument($file, $filenameBase, $dirPath, $metaData);
        }
    }

    public function storeProjectImage(Project $project, UploadedFile $file, array $seoData = [], bool $isPrimary = false)
    {
        return $this->processUpload($file, $project, 'gallery', ['is_primary' => $isPrimary]);
    }

    public function storeUnitImage($unit, UploadedFile $file, array $seoData = [])
    {
        return $this->processUpload($file, $unit, 'gallery');
    }

    public function storeBlogImage($blogPost, UploadedFile $file)
    {
         return $this->processUpload($file, $blogPost, 'default');
    }

    protected function processImage(UploadedFile $file, string $filenameBase, string $dirPath, array $metaData, $modelContext = null)
    {
        try {
            $image = $this->manager->read($file);
            $disk = $metaData['disk'];

            // SEO Alt Text Generation
            $altTexts = ['en' => '', 'ar' => ''];
            if ($modelContext && method_exists($this->altGenerator, 'generateForProject') && $metaData['context_type'] === 'project') {
                 $altTexts = $this->altGenerator->generateForProject($modelContext);
            } elseif ($modelContext && method_exists($this->altGenerator, 'generateForBlog') && $metaData['context_type'] === 'blog') {
                 $altTexts = $this->altGenerator->generateForBlog($modelContext);
            }

            // WEBP (Primary) - Optimized
            $webpFilename = $filenameBase . '.webp';
            $webpPath = $dirPath . '/' . $webpFilename;
            $encodedWebp = $image->toWebp(80);
            Storage::disk($disk)->put($webpPath, (string) $encodedWebp);

            $mediaFileWebp = MediaFile::create(array_merge($metaData, [
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

            // AVIF (Next Gen)
            try {
                $avifFilename = $filenameBase . '.avif';
                $avifPath = $dirPath . '/' . $avifFilename;
                $encodedAvif = $image->toAvif(80);
                Storage::disk($disk)->put($avifPath, (string) $encodedAvif);

                MediaFile::create(array_merge($metaData, [
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
                // AVIF might not be supported on this environment, ignore
            }

            // Thumbnails (Resize)
            $this->createThumbnail($image, $filenameBase, $dirPath, $metaData, $mediaFileWebp);

            // Watermark Logic
            $needsWatermark = !$metaData['is_private']
                              && in_array($metaData['context_type'], ['project', 'unit', 'listing'])
                              && in_array($metaData['collection_name'], ['gallery', 'default']);

            if ($needsWatermark) {
                 $this->createWatermarkedVariant($file, $filenameBase, $dirPath, $metaData, $mediaFileWebp, $altTexts);
            }

            return $mediaFileWebp;

        } catch (\Exception $e) {
            $this->logError($e, $file, $metaData['context_type'], $metaData['context_id']);
            throw $e;
        }
    }

    protected function createThumbnail($image, $filenameBase, $dirPath, $metaData, $parentMedia)
    {
        $thumbFilename = $filenameBase . '-thumb.webp';
        $thumbPath = $dirPath . '/' . $thumbFilename;

        // Resize to width 400, auto height
        $image->scale(width: 400);
        $encodedThumb = $image->toWebp(70);

        Storage::disk($metaData['disk'])->put($thumbPath, (string) $encodedThumb);

        MediaFile::create(array_merge($metaData, [
            'path' => $thumbPath,
            'original_filename' => $parentMedia->original_filename,
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'size_bytes' => strlen((string) $encodedThumb),
            'width' => $image->width(),
            'height' => $image->height(),
            'variant_role' => 'thumbnail',
            'variant_of_id' => $parentMedia->id,
        ]));
    }

    protected function createWatermarkedVariant($originalFile, $filenameBase, $dirPath, $metaData, $parentMedia, $altTexts)
    {
        $watermarkPath = storage_path('app/media/watermark.png');
        if (!file_exists($watermarkPath) && file_exists(public_path('watermark.png'))) {
             $watermarkPath = public_path('watermark.png');
        }

        if (file_exists($watermarkPath)) {
            $image = $this->manager->read($originalFile);
            $image->place($watermarkPath, 'bottom-right', 10, 10, 50);

            $wmFilename = $filenameBase . '-wm.webp';
            $wmPath = $dirPath . '/' . $wmFilename;
            $encodedWm = $image->toWebp(80);

            Storage::disk($metaData['disk'])->put($wmPath, (string) $encodedWm);

            MediaFile::create(array_merge($metaData, [
                'path' => $wmPath,
                'original_filename' => $originalFile->getClientOriginalName(),
                'mime_type' => 'image/webp',
                'extension' => 'webp',
                'size_bytes' => strlen((string) $encodedWm),
                'width' => $image->width(),
                'height' => $image->height(),
                'variant_role' => 'watermarked',
                'variant_of_id' => $parentMedia->id,
                'alt_en' => $altTexts['en'],
                'alt_ar' => $altTexts['ar'],
            ]));
        }
    }

    protected function processDocument(UploadedFile $file, string $filenameBase, string $dirPath, array $metaData)
    {
        $extension = $file->getClientOriginalExtension();
        $filename = "{$filenameBase}.{$extension}";
        $path = $file->storeAs($dirPath, $filename, ['disk' => $metaData['disk']]);

        return MediaFile::create(array_merge($metaData, [
            'path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'extension' => $extension,
            'size_bytes' => $file->getSize(),
            'width' => null,
            'height' => null,
            'variant_role' => 'original',
        ]));
    }

    protected function extractLocationData($model)
    {
        $data = [
            'country_id' => null,
            'region_id' => null,
            'city_id' => null,
            'district_id' => null,
            'project_id' => null,
        ];

        if ($model instanceof Project) {
            $data['country_id'] = $model->country_id;
            $data['region_id'] = $model->region_id;
            $data['city_id'] = $model->city_id;
            $data['district_id'] = $model->district_id;
            $data['project_id'] = $model->id;
        } elseif (method_exists($model, 'project') && $model->project) {
             $p = $model->project;
             $data['country_id'] = $p->country_id;
             $data['region_id'] = $p->region_id;
             $data['city_id'] = $p->city_id;
             $data['district_id'] = $p->district_id;
             $data['project_id'] = $p->id;
        }

        return $data;
    }

    protected function buildDirectoryPath($root, $type, $id, $collection, $locData, $model)
    {
        if (($type === 'project' || $locData['project_id']) && isset($model->country)) {
             $p = ($type === 'project') ? $model : $model->project;

             if ($p) {
                 $country = $p->country->code ?? 'xx';
                 $region = $p->region->slug ?? 'general';
                 $city = $p->city->slug ?? 'general';
                 $district = $p->district->slug ?? 'none';
                 $pSlug = $p->seo_slug_en ?? $p->slug ?? $p->id;

                 if ($type === 'project') {
                     return "{$root}/projects/{$country}/{$region}/{$city}/{$district}/{$pSlug}/{$collection}";
                 }

                 $subSlug = $model->slug ?? $model->id;
                 return "{$root}/{$type}s/{$country}/{$region}/{$city}/{$district}/{$pSlug}/{$subSlug}/{$collection}";
             }
        }

        $slug = $model->slug ?? $model->id;
        return "{$root}/{$type}s/{$slug}/{$collection}";
    }

    protected function generateFilename(UploadedFile $file, $model, $collection)
    {
        $base = $model->seo_slug_en ?? $model->slug ?? $model->id ?? 'file';
        $base = Str::slug($base);
        $random = Str::random(6);
        return "{$base}-{$collection}-{$random}";
    }

    protected function logError($e, $file, $context, $id)
    {
        Log::error("Media Processing Failed: " . $e->getMessage(), [
            'file' => $file->getClientOriginalName(),
            'context' => $context,
            'id' => $id
        ]);
    }
}
