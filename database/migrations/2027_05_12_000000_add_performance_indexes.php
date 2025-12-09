<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1) projects table: Map viewport and location filters
        Schema::table('projects', function (Blueprint $table) {
            // Optimize map viewport queries where we select projects within a lat/lng box
            $table->index(['lat', 'lng'], 'projects_lat_lng_index');

            // Optimize filtering by city independent of country/region
            $table->index('city_id', 'projects_city_id_index');

            // Optimize filtering by district independent of city
            $table->index('district_id', 'projects_district_id_index');
        });

        // 2) locations (countries, regions, cities, districts): Spatial queries
        $locationTables = ['countries', 'regions', 'cities', 'districts'];
        foreach ($locationTables as $tableName) {
            // Ensure table and column exist before adding spatial index
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'boundary')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    // Add SPATIAL index on boundary for fast geometric intersection/containment queries
                    $table->spatialIndex('boundary', $tableName . '_boundary_spatial_index');
                });
            }
        }

        // 3) units table: Combined price and area filters
        Schema::table('units', function (Blueprint $table) {
            // Optimize filtering units by price range and area range simultaneously.
            // We check for 'built_up_area' first (preferred from schema.sql), then fallback to 'area'.
            if (Schema::hasColumn('units', 'built_up_area')) {
                $table->index(['price', 'built_up_area'], 'units_price_built_up_area_index');
            } elseif (Schema::hasColumn('units', 'area')) {
                $table->index(['price', 'area'], 'units_price_area_index');
            }
        });

        // 4) developers table: Active developers ordered by name
        Schema::table('developers', function (Blueprint $table) {
            // Optimize "active developers ordered by name" queries
            $table->index(['is_active', 'name'], 'developers_active_name_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1) projects table
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex('projects_lat_lng_index');
            $table->dropIndex('projects_city_id_index');
            $table->dropIndex('projects_district_id_index');
        });

        // 2) locations
        $locationTables = ['countries', 'regions', 'cities', 'districts'];
        foreach ($locationTables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'boundary')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    // To be safe against "index does not exist" errors on rollback
                    try {
                        $table->dropSpatialIndex($tableName . '_boundary_spatial_index');
                    } catch (\Throwable $e) {}
                });
            }
        }

        // 3) units table
        Schema::table('units', function (Blueprint $table) {
            try {
                $table->dropIndex('units_price_built_up_area_index');
            } catch (\Throwable $e) {}

            try {
                $table->dropIndex('units_price_area_index');
            } catch (\Throwable $e) {}
        });

        // 4) developers table
        Schema::table('developers', function (Blueprint $table) {
            $table->dropIndex('developers_active_name_index');
        });
    }
};
