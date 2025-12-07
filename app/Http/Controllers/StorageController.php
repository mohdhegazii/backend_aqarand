<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StorageController extends Controller
{
    /**
     * Fallback for serving files from storage/app/public when symlink is missing.
     * This handles routes like /media-fallback/{path}
     */
    public function serve(Request $request, $path)
    {
        $path = ltrim($path, '/');

        // Security check: prevent traversing up directories
        if (str_contains($path, '..')) {
            abort(404);
        }

        // We assume the route is /media-fallback/{path}, so $path is relative to storage/app/public
        $disk = Storage::disk('public');

        if (!$disk->exists($path)) {
            abort(404);
        }

        return $disk->response($path);
    }
}
