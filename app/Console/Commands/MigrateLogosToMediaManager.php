<?php

namespace App\Console\Commands;

use App\Models\Developer;
use App\Models\MediaFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MigrateLogosToMediaManager extends Command
{
    protected $signature = 'media:migrate-logos {--dry-run : Run without making changes}';
    protected $description = 'Migrate legacy developer logos to Media Manager';

    public function handle()
    {
        $this->info('Starting logo migration...');

        // Fetch developers with any potential logo column populated
        $developers = Developer::where(function($q) {
            $q->whereNotNull('logo_path')
              ->orWhereNotNull('logo')
              ->orWhereNotNull('logo_url');
        })->get();

        $count = 0;
        $dryRun = $this->option('dry-run');

        foreach ($developers as $developer) {
            // Determine the active logo path
            // Priority: logo_path > logo > logo_url (if local)
            $rawPath = $developer->logo_path ?? $developer->logo ?? $developer->logo_url;

            if (empty($rawPath)) {
                continue;
            }

            // Skip if already has media linked
            if ($developer->mediaLinks()->where('role', 'logo')->exists()) {
                continue;
            }

            // If it's a full URL, we might skip or try to download (out of scope for now, usually local dev/prod urls)
            if (Str::startsWith($rawPath, ['http://', 'https://'])) {
                $this->warn("Skipping URL-based logo for Developer #{$developer->id}: {$rawPath} (Download logic not implemented)");
                continue;
            }

            // Clean path
            $path = ltrim($rawPath, '/');
            if (Str::startsWith($path, 'storage/')) {
                $path = substr($path, 8); // Remove storage/ prefix to get relative path in public disk
            }

            // Check if file exists on public disk
            if (!Storage::disk('public')->exists($path)) {
                $this->warn("File not found on public disk for Developer #{$developer->id}: {$path}");
                continue;
            }

            if ($dryRun) {
                $this->info("[Dry Run] Would migrate logo for Developer #{$developer->id}: {$path}");
                continue;
            }

            $this->info("Migrating logo for Developer #{$developer->id}: {$path}");

            try {
                // Get file details
                $mime = Storage::disk('public')->mimeType($path);
                $size = Storage::disk('public')->size($path);
                $filename = basename($path);
                $ext = pathinfo($filename, PATHINFO_EXTENSION);

                // Create MediaFile
                // We register the existing file in place
                $mediaFile = MediaFile::create([
                    'disk' => 'public',
                    'path' => $path,
                    'original_filename' => $filename, // Use original_filename based on schema
                    'original_name' => $filename,     // Fallback/Legacy field if exists
                    'extension' => $ext,
                    'mime_type' => $mime,
                    'size_bytes' => $size,
                    'type' => str_starts_with($mime, 'image/') ? 'image' : 'other',
                    'seo_slug' => Str::slug($developer->name_en ?? $developer->name ?? 'developer-logo-' . $developer->id),
                    'alt_text' => $developer->name_en ?? $developer->name,
                    'uploaded_by_id' => 1, // System/Admin
                ]);

                // Attach to Developer
                $developer->attachMedia($mediaFile, 'logo');
                $count++;

            } catch (\Exception $e) {
                $this->error("Failed to migrate Developer #{$developer->id}: " . $e->getMessage());
            }
        }

        $this->info("Migration complete. Migrated {$count} logos.");
    }
}
