<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MediaUploadRequest;
use App\Jobs\ProcessMediaFileJob;
use App\Models\MediaFile;
use App\Services\Media\MediaDiskResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MediaApiController extends Controller
{
    /**
     * Handle media upload.
     * Supports both single file ('file') and multiple files ('files[]').
     */
    public function upload(MediaUploadRequest $request, MediaDiskResolver $diskResolver)
    {
        // Gather files (normalize to array to support multiple uploads)
        $files = [];
        if ($request->hasFile('files')) {
            $files = $request->file('files');
        } elseif ($request->hasFile('file')) {
            $files = [$request->file('file')];
        }

        $uploadedMedia = [];
        $disk = config('filesystems.default', 'public');

        // Ensure we are not using 'local' (private) if public is intended
        // If config is 'local' (private), but we are uploading public media, prefer 'public'
        if ($disk === 'local' && $request->input('is_private') != '1') {
             $disk = 'public';
        }

        foreach ($files as $file) {
            // Determine type
            $mime = $file->getMimeType();
            $type = 'other';
            if (str_starts_with($mime, 'image/')) {
                $type = 'image';
            } elseif ($mime === 'application/pdf') {
                $type = 'pdf';
            } elseif (str_starts_with($mime, 'video/')) {
                $type = 'video';
            }

            // Generate base SEO slug
            $nameWithoutExt = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $slugBase = $nameWithoutExt;

            if ($request->filled('slug') && count($files) === 1) {
                // Only apply explicit slug if single file, otherwise use filename
                $slugBase .= '-' . $request->input('slug');
            }
            $seoSlug = Str::slug($slugBase);

            // Store directly to permanent disk
            $uploadPath = 'media/' . date('Y/m');
            try {
                $path = $file->store($uploadPath, ['disk' => $disk]);

                if (!$path) {
                    throw new \Exception("Failed to store file on disk: $disk");
                }

                // Create MediaFile record with populated disk/path
                $mediaFile = MediaFile::create([
                    'original_name' => $file->getClientOriginalName(),
                    'extension' => $file->getClientOriginalExtension(),
                    'mime_type' => $mime,
                    'type' => $type,
                    'seo_slug' => $seoSlug,
                    'uploaded_by_id' => auth()->id(),
                    'alt_text' => $request->input('alt_text'), // Will apply to all if multiple
                    'disk' => $disk,
                    'path' => $path,
                    'size_bytes' => $file->getSize(),
                    'is_private' => $request->input('is_private') == '1'
                ]);

                // Context for processing
                $context = [
                    'entity' => $request->input('entity_type'),
                    'entity_id' => $request->input('entity_id'),
                    'country' => $request->input('country'),
                    'city' => $request->input('city'),
                    'slug' => $request->input('slug'),
                ];

                // Dispatch Job with a COPY of the file in media_tmp
                // ProcessMediaFileJob expects the file in media_tmp to process it (e.g. create thumbnails)
                // We copy it so the job can work as designed without messing with our permanent file
                $tempDisk = 'media_tmp';
                $tempFilename = uniqid('proc_') . '.' . $file->getClientOriginalExtension();

                // Copy from disk to media_tmp
                $content = Storage::disk($disk)->get($path);
                Storage::disk($tempDisk)->put($tempFilename, $content);

                // Dispatch with the temp path
                ProcessMediaFileJob::dispatch($mediaFile->id, $tempFilename, $context);

                $uploadedMedia[] = [
                    'id' => $mediaFile->id,
                    'status' => 'uploaded',
                    'type' => $type,
                    'original_name' => $mediaFile->original_name,
                    'seo_slug' => $mediaFile->seo_slug,
                    'url' => $mediaFile->url,
                    'thumb_url' => $mediaFile->url,
                ];

            } catch (\Exception $e) {
                Log::error('Media Upload Error: ' . $e->getMessage());
                // Continue with next file
            }
        }

        if (empty($uploadedMedia) && count($files) > 0) {
            return response()->json(['message' => 'Upload failed for all files'], 500);
        }

        // Return standardized JSON response
        return response()->json([
            'uploaded' => $uploadedMedia,
            // Legacy single-file response support
            'media_id' => $uploadedMedia[0]['id'] ?? null,
            'status' => 'uploaded',
            'type' => $uploadedMedia[0]['type'] ?? 'other',
            'original_name' => $uploadedMedia[0]['original_name'] ?? '',
            'seo_slug' => $uploadedMedia[0]['seo_slug'] ?? '',
        ]);
    }

    public function index(Request $request)
    {
        // Handle explicit ID fetching for previews
        if ($request->has('ids')) {
            $idsParam = $request->input('ids');

            if (!$idsParam) {
                return response()->json([]);
            }

            $ids = collect(explode(',', $idsParam))
                ->filter()
                ->map(fn($id) => (int) $id);

            if ($ids->isEmpty()) {
                return response()->json([]);
            }

            $mediaFiles = MediaFile::whereIn('id', $ids)
                ->with('conversions')
                ->orderByRaw('FIELD(id, ' . implode(',', $ids->toArray()) . ')')
                ->get();

            // Transform collection
            $data = $mediaFiles->map(function ($item) {
                return [
                    'id' => $item->id,
                    'type' => $item->type,
                    'original_name' => $item->original_name,
                    'seo_slug' => $item->seo_slug,
                    'alt_text' => $item->alt_text,
                    'disk' => $item->disk,
                    'path' => $item->path,
                    'url' => $item->url,
                    'created_at' => $item->created_at->toIso8601String(),
                    'variants' => $item->variants,
                ];
            });

            return response()->json($data);
        }

        $query = MediaFile::query();

        // Type filter
        if ($request->filled('type') && $request->input('type') !== 'all') {
            $query->where('type', $request->input('type'));
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('original_name', 'LIKE', "%{$search}%")
                  ->orWhere('seo_slug', 'LIKE', "%{$search}%")
                  ->orWhere('alt_text', 'LIKE', "%{$search}%")
                  ->orWhere('title', 'LIKE', "%{$search}%");
            });
        }

        // Exclude soft deleted is default behavior
        $query->orderBy('created_at', 'desc');
        $query->with('conversions');

        $perPage = $request->input('per_page', 20);
        $mediaFiles = $query->paginate($perPage);

        // Transform collection
        $data = $mediaFiles->getCollection()->map(function ($item) {
            return [
                'id' => $item->id,
                'type' => $item->type,
                'original_name' => $item->original_name,
                'seo_slug' => $item->seo_slug,
                'alt_text' => $item->alt_text,
                'disk' => $item->disk,
                'path' => $item->path,
                'url' => $item->url,
                'created_at' => $item->created_at->toIso8601String(),
                'variants' => $item->variants,
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $mediaFiles->currentPage(),
                'per_page' => $mediaFiles->perPage(),
                'total' => $mediaFiles->total(),
                'last_page' => $mediaFiles->lastPage(),
            ],
        ]);
    }

    public function show($id)
    {
        $mediaFile = MediaFile::with(['conversions', 'tags'])->findOrFail($id);

        return response()->json([
            'id' => $mediaFile->id,
            'type' => $mediaFile->type,
            'original_name' => $mediaFile->original_name,
            'seo_slug' => $mediaFile->seo_slug,
            'mime_type' => $mediaFile->mime_type,
            'alt_text' => $mediaFile->alt_text,
            'title' => $mediaFile->title,
            'caption' => $mediaFile->caption,
            'url' => $mediaFile->url,
            'variants' => $mediaFile->variants,
            'created_at' => $mediaFile->created_at->toIso8601String(),
            'tags' => $mediaFile->tags,
        ]);
    }

    public function destroy($id)
    {
        $mediaFile = MediaFile::findOrFail($id);

        // Soft delete
        $mediaFile->delete();

        return response()->json(['success' => true, 'media_id' => $id]);
    }
}
