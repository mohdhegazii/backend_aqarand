<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoMeta extends Model
{
    use HasFactory;

    protected $table = 'seo_meta';

    protected $fillable = [
        'seoable_id',
        'seoable_type',
        'meta_title',
        'meta_description',
        'focus_keyphrase',
        'meta_data',
    ];

    protected $casts = [
        'meta_data' => 'array',
    ];

    public function seoable()
    {
        return $this->morphTo();
    }
}
