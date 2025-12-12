<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\Traits\HasMedia;

class Project extends Model
{
    use HasFactory;
    use HasMedia;
    // use SoftDeletes; // Schema does not show deleted_at, assuming is_active usage instead or standard deletion

    protected $table = 'projects';

    protected $fillable = [
        'developer_id',
        'country_id',
        'region_id',
        'city_id',
        'district_id',
        'location_project_id',
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
        'map_lat',
        'map_lng',
        'map_zoom',
        'address_text',
        'launch_date', // Project Launch Date
        'description_short_ar',
        'description_short_en',
        'financial_summary_ar',
        'financial_summary_en',
        'payment_profiles',
        'phases',
        'brochure_url',
        'brochure_file_path',
        'hero_image_url',
        'gallery',
        'gallery_images',
        'master_plan_image',
        'video_url',
        'video_urls',
        'map_polygon',
        'project_boundary_geojson',
        'boundary_geojson', // Virtual attribute
        'meta_title',
        'meta_description',
        'main_keyword',
        'title_ar',
        'title_en',
        'description_ar',
        'description_en',
        'is_active',
        // 'description_long', // Legacy
        // 'tagline', // Legacy
    ];

    protected $casts = [
        'gallery' => 'array',
        'map_polygon' => 'array',
        'project_boundary_geojson' => 'array',
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
        'launch_date' => 'date',
        'map_lat' => 'decimal:7',
        'map_lng' => 'decimal:7',
        'map_zoom' => 'integer',
        'payment_profiles' => 'array',
        'phases' => 'array',
        'gallery_images' => 'array',
        'video_urls' => 'array',
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

    public function locationProject()
    {
        return $this->belongsTo(Project::class, 'location_project_id');
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

    /**
     * Get the project boundary GeoJSON.
     * Use 'project_boundary_geojson' as primary source, 'map_polygon' as fallback.
     *
     * @return array|null
     */
    public function getBoundaryGeojsonAttribute()
    {
        return $this->project_boundary_geojson ?? $this->map_polygon;
    }

    /**
     * Set the project boundary GeoJSON.
     * Syncs both 'project_boundary_geojson' and 'map_polygon' columns.
     *
     * @param array|string|null $value
     * @return void
     */
    public function setBoundaryGeojsonAttribute($value)
    {
        // Ensure we are working with an array or null
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            } else {
                 // if invalid json, might treat as null or keep as is if it was intended?
                 // usually means empty string or bad json
                 if (empty($value)) $value = null;
            }
        }

        $this->attributes['project_boundary_geojson'] = $value ? json_encode($value) : null;
        $this->attributes['map_polygon'] = $value ? json_encode($value) : null;
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
