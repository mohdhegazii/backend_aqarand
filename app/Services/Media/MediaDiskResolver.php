<?php

namespace App\Services\Media;

use App\Models\MediaSetting;
use Illuminate\Support\Facades\Cache;

/**
 * Service to resolve storage disks based on MediaSettings configuration.
 */
class MediaDiskResolver
{
    /**
     * Get the default disk for storing media files.
     *
     * @return string
     */
    public function getDefaultMediaDisk(): string
    {
        $settings = $this->getSettings();
        return $settings->disk_default ?? 's3_media_local';
    }

    /**
     * Get the disk for system assets (if different).
     *
     * @return string
     */
    public function getSystemAssetsDisk(): string
    {
        $settings = $this->getSettings();
        return $settings->disk_system_assets ?? 'local_work';
    }

    /**
     * Get the temporary disk for uploads and processing.
     *
     * @return string
     */
    public function getTmpDisk(): string
    {
        return 'media_tmp';
    }

    /**
     * Retrieve the single media settings record, cached if possible.
     *
     * @return MediaSetting|object
     */
    protected function getSettings()
    {
        // Cache settings to avoid repeated DB hits during bulk processing
        return Cache::remember('media_settings.active', 600, function () {
            return MediaSetting::first() ?? (object) [];
        });
    }
}
