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
    ];

    public function regions()
    {
        return $this->hasMany(Region::class);
    }
}
