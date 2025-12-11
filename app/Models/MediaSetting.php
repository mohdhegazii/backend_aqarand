<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaSetting extends Model
{
    use HasFactory;

    protected $table = 'media_settings';

    protected $fillable = [
        'disk_default',
        'disk_system_assets',
        'image_max_size_mb',
        'pdf_max_size_mb',
        'image_quality',
        'image_max_width',
        'generate_webp',
        'generate_thumb',
        'thumb_width',
        'medium_width',
        'large_width',
    ];

    protected $casts = [
        'image_max_size_mb' => 'integer',
        'pdf_max_size_mb' => 'integer',
        'image_quality' => 'integer',
        'image_max_width' => 'integer',
        'generate_webp' => 'boolean',
        'generate_thumb' => 'boolean',
        'thumb_width' => 'integer',
        'medium_width' => 'integer',
        'large_width' => 'integer',
    ];
}
