<?php

namespace App\Services\Media\DTO;

class MediaProcessingResult
{
    /**
     * @var array
     * Structure: [
     *   [
     *     'conversion_name' => string (e.g., 'original', 'thumb', 'medium'),
     *     'tmp_path' => string (relative to media_tmp disk),
     *     'width' => int|null,
     *     'height' => int|null,
     *     'size_bytes' => int,
     *     'extension' => string
     *   ]
     * ]
     */
    public array $variants = [];

    public function addVariant(string $name, string $tmpPath, ?int $width, ?int $height, int $size, string $extension): void
    {
        $this->variants[] = [
            'conversion_name' => $name,
            'tmp_path' => $tmpPath,
            'width' => $width,
            'height' => $height,
            'size_bytes' => $size,
            'extension' => $extension,
        ];
    }
}
