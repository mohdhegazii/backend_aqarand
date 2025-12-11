<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MediaTag extends Model
{
    use HasFactory;

    protected $table = 'media_tags';

    protected $fillable = [
        'name',
        'slug',
    ];

    public function mediaFiles(): BelongsToMany
    {
        return $this->belongsToMany(MediaFile::class, 'media_file_tag', 'media_tag_id', 'media_file_id');
    }
}
