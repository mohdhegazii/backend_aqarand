<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    use HasFactory;

    protected $table = 'blog_posts';

    protected $fillable = [
        'title_en',
        'title_ar',
        'slug_en',
        'slug_ar',
        'content_en',
        'content_ar',
        'published_at',
        'status',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function getDisplayNameAttribute()
    {
        return app()->getLocale() === 'ar' ? ($this->title_ar ?? $this->title_en) : ($this->title_en ?? $this->title_ar);
    }
}
