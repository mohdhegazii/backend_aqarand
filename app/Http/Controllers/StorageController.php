<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StorageController extends Controller
{
    /**
     * Fallback for serving files from storage/app/public when symlink is missing.
     * This handles routes like /storage/{path}
     */
    public function serve(Request $request, $path)
    {
        // Security check: prevent traversing up directories
        if (str_contains($path, '..')) {
            abort(404);
        }

        // We assume the route is /storage/{path}, so $path is relative to storage/app/public
        $disk = Storage::disk('public');

        if (!$disk->exists($path)) {
            abort(404);
        }

        return $disk->response($path);
    }
}
