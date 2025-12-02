<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $table = 'countries';

    protected $fillable = [
        'code',
        'name_en',
        'name_local',
        'lat',
        'lng',
        'is_active',
    ];

    protected $casts = [
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'is_active' => 'boolean',
    ];

    public function regions()
    {
        return $this->hasMany(Region::class);
    }

    public function getName($locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        if ($locale === 'ar') {
            return $this->name_local ?? $this->name_en;
        }
        return $this->name_en;
    }
}
