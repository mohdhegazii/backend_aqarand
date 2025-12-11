<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MediaLink extends Model
{
    use HasFactory;

    protected $table = 'media_links';

    protected $fillable = [
        'media_file_id',
        'model_type',
        'model_id',
        'role',
        'ordering',
    ];

    protected $casts = [
        'ordering' => 'integer',
        'model_id' => 'integer',
        'media_file_id' => 'integer',
    ];

    public function mediaFile(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'media_file_id');
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
