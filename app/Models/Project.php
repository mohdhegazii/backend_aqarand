<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory;
    // use SoftDeletes; // Schema does not show deleted_at, assuming is_active usage instead or standard deletion

    protected $table = 'projects';

    protected $fillable = [
        'developer_id',
        'country_id',
        'region_id',
        'city_id',
        'district_id',
        'name', // Keep for backward compatibility/search if needed
        'name_ar',
        'name_en',
        'slug',
        'construction_status',
        'project_area_value',
        'project_area_unit',
        'is_part_of_master_project',
        'master_project_id',
        'sales_launch_date',
        'is_featured',
        'is_top_project',
        'include_in_sitemap',
        'status', // publish status
        'delivery_year',
        'total_area',
        'built_up_ratio',
        'total_units',
        'min_price',
        'max_price',
        'min_bua',
        'max_bua',
        'lat',
        'lng',
        'address_text',
        'brochure_url',
        'hero_image_url',
        'gallery',
        'video_url',
        'map_polygon',
        'meta_title',
        'meta_description',
        'main_keyword',
        'title_ar',
        'title_en',
        'description_ar',
        'description_en',
        'is_active',
        'boundary_polygon',
        // 'description_long', // Legacy
        // 'tagline', // Legacy
    ];

    protected $casts = [
        'gallery' => 'array',
        'map_polygon' => 'array',
        'boundary_polygon' => 'array',
        'sales_launch_date' => 'date',
        'is_part_of_master_project' => 'boolean',
        'is_featured' => 'boolean',
        'is_top_project' => 'boolean',
        'include_in_sitemap' => 'boolean',
        'is_active' => 'boolean',
        'project_area_value' => 'decimal:2',
        'min_price' => 'decimal:2',
        'max_price' => 'decimal:2',
        'min_bua' => 'decimal:2',
        'max_bua' => 'decimal:2',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
    ];

    // Relations

    public function developer()
    {
        return $this->belongsTo(Developer::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function masterProject()
    {
        return $this->belongsTo(Project::class, 'master_project_id');
    }

    public function subProjects()
    {
        return $this->hasMany(Project::class, 'master_project_id');
    }

    public function faqs()
    {
        return $this->hasMany(ProjectFaq::class)->orderBy('sort_order');
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'project_amenity', 'project_id', 'amenity_id');
    }

    public function propertyModels()
    {
        return $this->hasMany(PropertyModel::class);
    }

    // Scopes

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Accessors

    public function getNameAttribute($value)
    {
        // Fallback or prefer localized
        if (app()->getLocale() == 'ar' && !empty($this->name_ar)) {
            return $this->name_ar;
        }
        if (app()->getLocale() == 'en' && !empty($this->name_en)) {
            return $this->name_en;
        }
        return $value; // Fallback to legacy 'name' column
    }

    public function getDisplayNameAttribute()
    {
        return $this->name;
    }

    public static function generateSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = Str::slug($base);

        $original = $slug;
        $counter = 1;

        while (
            static::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $original.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
