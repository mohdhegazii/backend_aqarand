<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Segment extends Model
{
    use HasFactory;

    protected $table = 'segments';

    protected $fillable = [
        'name_en',
        'name_ar',
        'slug',
        'is_active',
        'image_path',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the localized name.
     */
    public function getName($locale = null)
    {
        $locale = $locale ?: app()->getLocale();
        return $locale === 'ar' ? $this->name_ar : $this->name_en;
    }

    /**
     * A segment has many categories.
     */
    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function getDisplayNameAttribute(): string
    {
        $locale = app()->getLocale();

        if ($locale === 'ar' && !empty($this->name_ar)) {
            return $this->name_ar;
        }

        return $this->name_en ?? $this->name_ar ?? '';
    }
}
