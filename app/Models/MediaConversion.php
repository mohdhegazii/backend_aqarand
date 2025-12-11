<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaConversion extends Model
{
    use HasFactory;

    protected $table = 'media_conversions';

    protected $fillable = [
        'media_file_id',
        'conversion_name',
        'disk',
        'path',
        'size_bytes',
        'width',
        'height',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    public function mediaFile(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'media_file_id');
    }
}
