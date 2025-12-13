<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MediaFile;
use Illuminate\Support\Str;

class NormalizeMediaPaths extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:normalize-paths {--dry-run : Only show what would be changed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Normalize media paths to be relative (stripping storage prefix)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Scanning MediaFiles for path normalization...");

        $files = MediaFile::all();
        $count = 0;

        foreach ($files as $file) {
            $path = $file->path;
            $newPath = $path;

            // Remove full URL prefix if present
            if (Str::startsWith($path, 'http')) {
                // Try to strip everything up to /storage/
                $parts = explode('/storage/', $path);
                if (count($parts) > 1) {
                    $newPath = $parts[1]; // Keep everything after /storage/
                }
            }

            // Remove leading /storage/ or storage/
            if (Str::startsWith($newPath, '/storage/')) {
                $newPath = substr($newPath, 9);
            } elseif (Str::startsWith($newPath, 'storage/')) {
                $newPath = substr($newPath, 8);
            }

            // Remove leading slash if any
            $newPath = ltrim($newPath, '/');

            if ($path !== $newPath) {
                if ($this->option('dry-run')) {
                    $this->info("[DRY RUN] Would change ID {$file->id}: '{$path}' -> '{$newPath}'");
                } else {
                    $file->path = $newPath;
                    $file->saveQuietly(); // Don't trigger updated_at or events if possible
                    $this->info("Changed ID {$file->id}: '{$path}' -> '{$newPath}'");
                }
                $count++;
            }
        }

        $this->info("Normalization complete. {$count} records processed.");
    }
}
