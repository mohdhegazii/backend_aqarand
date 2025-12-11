<?php

namespace App\Jobs;

use App\Models\MediaFile;
use App\Services\Media\MediaProcessingService;
use App\Services\Media\MediaStorageService;
use App\Services\Media\MediaDiskResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessMediaFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $mediaFileId;
    protected string $tmpPath;
    protected array $context;

    /**
     * Create a new job instance.
     *
     * @param int $mediaFileId
     * @param string $tmpPath Relative path on media_tmp disk
     * @param array $context
     */
    public function __construct(int $mediaFileId, string $tmpPath, array $context = [])
    {
        $this->mediaFileId = $mediaFileId;
        $this->tmpPath = $tmpPath;
        $this->context = $context;
    }

    /**
     * Execute the job.
     *
     * @param MediaProcessingService $processingService
     * @param MediaStorageService $storageService
     * @param MediaDiskResolver $diskResolver
     * @return void
     */
    public function handle(
        MediaProcessingService $processingService,
        MediaStorageService $storageService,
        MediaDiskResolver $diskResolver
    ): void {
        try {
            $media = MediaFile::findOrFail($this->mediaFileId);

            // Determine processing type
            $mime = $media->mime_type;
            $isProcessed = false;

            if (str_starts_with($mime, 'image/')) {
                $result = $processingService->processImage($this->tmpPath, $media);
                $isProcessed = true;
            } elseif ($mime === 'application/pdf') {
                $result = $processingService->processPdf($this->tmpPath, $media);
                // For PDF, currently processPdf returns the original tmp path as the variant
                // so we don't want to delete it until after storage.
            } else {
                Log::warning("ProcessMediaFileJob: Unsupported mime type {$mime} for media ID {$media->id}. Skipping processing.");
                return;
            }

            // Persist
            $storageService->persistProcessedMedia($media, $result, $this->context);

            // Clean up the original uploaded file
            // Note: MediaStorageService cleans up the *variant* tmp paths (which it moves).
            // For Images: processImage creates NEW tmp files for variants. The original $this->tmpPath remains and must be deleted here.
            // For PDFs: processPdf uses $this->tmpPath as the variant path. MediaStorageService moves (reads/puts/deletes) it.
            // So we need to check if $this->tmpPath still exists and delete it.

            $tmpDisk = Storage::disk($diskResolver->getTmpDisk());
            if ($tmpDisk->exists($this->tmpPath)) {
                $tmpDisk->delete($this->tmpPath);
            }

            Log::info("ProcessMediaFileJob: Successfully processed media ID {$media->id}");

        } catch (Throwable $e) {
            Log::error("ProcessMediaFileJob: Failed to process media ID {$this->mediaFileId}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'tmpPath' => $this->tmpPath
            ]);

            $this->fail($e);
        }
    }
}
