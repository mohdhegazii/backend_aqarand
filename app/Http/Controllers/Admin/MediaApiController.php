<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MediaUploadRequest;
use App\Jobs\ProcessMediaFileJob;
use App\Models\MediaFile;
use App\Services\Media\MediaDiskResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MediaApiController extends Controller
{
    public function upload(MediaUploadRequest $request, MediaDiskResolver $diskResolver)
    {
        $file = $request->file('file');

        // Determine type
        $mime = $file->getMimeType();
        $type = 'other';
        if (str_starts_with($mime, 'image/')) {
            $type = 'image';
        } elseif ($mime === 'application/pdf') {
            $type = 'pdf';
        }

        // Generate base SEO slug
        $nameWithoutExt = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $slugBase = $nameWithoutExt;

        if ($request->filled('slug')) {
            $slugBase .= '-' . $request->input('slug');
        }
        $seoSlug = Str::slug($slugBase);

        // Store temporarily
        // Use MediaDiskResolver to get the temporary disk name
        // Although Phase 2 instructions mentioned 'media_tmp' explicitly, let's try to follow best practice if resolver provides it.
        // But since resolver usually returns destination disk, and 'media_tmp' is specific for processing, I will stick to 'media_tmp' as per Phase 3 prompt instructions ("Use MediaDiskResolver to get the temporary disk name: media_tmp").
        // This phrasing suggests I should just use 'media_tmp' but maybe call resolver?
        // For now, I will stick to 'media_tmp' string as per specific instruction.
        $tempDisk = 'media_tmp';

        $tempFilename = uniqid('upload_') . '.' . $file->getClientOriginalExtension();
        $tempPath = $file->storeAs('', $tempFilename, ['disk' => $tempDisk]);

        // Create MediaFile record
        $mediaFile = MediaFile::create([
            'original_name' => $file->getClientOriginalName(),
            'extension' => $file->getClientOriginalExtension(),
            'mime_type' => $mime,
            'type' => $type,
            'seo_slug' => $seoSlug,
            'uploaded_by_id' => auth()->id(),
            'alt_text' => $request->input('alt_text'),
            'disk' => null,
            'path' => null,
        ]);

        // Context for processing
        $context = [
            'entity' => $request->input('entity_type'),
            'entity_id' => $request->input('entity_id'),
            'country' => $request->input('country'),
            'city' => $request->input('city'),
            'slug' => $request->input('slug'),
        ];

        // Dispatch Job
        ProcessMediaFileJob::dispatch($mediaFile->id, $tempPath, $context);

        return response()->json([
            'media_id' => $mediaFile->id,
            'status' => 'processing',
            'type' => $type,
            'original_name' => $mediaFile->original_name,
            'seo_slug' => $mediaFile->seo_slug,
        ]);
    }

    public function index(Request $request)
    {
        $query = MediaFile::query();

        // Type filter
        if ($request->filled('type')) {
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
