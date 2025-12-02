<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $table = 'cities';

    protected $fillable = [
        'region_id',
        'name_en',
        'name_local',
        'slug',
        'lat',
        'lng',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
    ];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function districts()
    {
        return $this->hasMany(District::class);
    }

    /**
     * Get the name based on the locale.
     *
     * @param string|null $locale
     * @return string
     */
    public function getName($locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        if ($locale === 'ar') {
            return $this->name_local ?? $this->name_en;
        }
        return $this->name_en;
    }
}
