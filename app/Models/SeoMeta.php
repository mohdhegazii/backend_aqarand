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
        // Generic/Old fields (kept for compatibility)
        'meta_title',
        'meta_description',
        'focus_keyphrase',
        // Bilingual fields
        'meta_title_en',
        'meta_description_en',
        'focus_keyphrase_en',
        'meta_title_ar',
        'meta_description_ar',
        'focus_keyphrase_ar',
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
