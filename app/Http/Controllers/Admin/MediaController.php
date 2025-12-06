<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Admin\StoreMediaRequest;
use App\Services\Media\MediaProcessor;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $query = MediaFile::query();

        // Filters
        if ($request->filled('context_type')) {
            $query->where('context_type', $request->context_type);
        }
        if ($request->filled('variant_role')) {
            $query->where('variant_role', $request->variant_role);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('original_filename', 'like', "%{$search}%")
                  ->orWhere('alt_en', 'like', "%{$search}%")
                  ->orWhere('alt_ar', 'like', "%{$search}%");
            });
        }

        // Location filters
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $mediaFiles = $query->latest()->paginate(20);

        return view('admin.media.index', compact('mediaFiles'));
    }

    public function show(MediaFile $mediaFile)
    {
        return view('admin.media.show', compact('mediaFile'));
    }

    public function update(Request $request, MediaFile $mediaFile)
    {
        $validated = $request->validate([
            'alt_en' => 'nullable|string|max:255',
            'alt_ar' => 'nullable|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'title_ar' => 'nullable|string|max:255',
        ]);

        $mediaFile->update($validated);

        return redirect()->route($this->adminRoutePrefix().'media.show', $mediaFile)
            ->with('success', __('admin.updated_successfully'));
    }

    public function destroy(MediaFile $mediaFile)
    {
        // Delete physical file
        if (Storage::disk($mediaFile->disk)->exists($mediaFile->path)) {
            Storage::disk($mediaFile->disk)->delete($mediaFile->path);
        }

        // If it has variants, delete them too (or model cascade will handle DB, but we need to delete files)
        foreach ($mediaFile->variants as $variant) {
            if (Storage::disk($variant->disk)->exists($variant->path)) {
                Storage::disk($variant->disk)->delete($variant->path);
            }
            $variant->delete();
        }

        $mediaFile->delete();

        // Check if request expects JSON (for AJAX delete in media manager)
        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route($this->adminRoutePrefix().'media.index')
            ->with('success', __('admin.deleted_successfully'));
    }

    public function download(MediaFile $mediaFile)
    {
        // Security check
        $this->authorize('view', $mediaFile);
        // Note: You need a Policy for MediaFile, or remove this line if you rely on route middleware
        // Since strict policies might not be set up, ensure strictly Admin access via middleware (handled in routes/web.php)

        if (!Storage::disk($mediaFile->disk)->exists($mediaFile->path)) {
            abort(404);
        }

        return Storage::disk($mediaFile->disk)->download($mediaFile->path, $mediaFile->original_filename);
    }
}
