<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Adds performance indexes to various tables.
 * See docs/performance-guidelines.md for details on why these indexes were chosen.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1) projects table: Map viewport and location filters
        if (Schema::hasTable('projects')) {
            // We separate each index into its own Schema::table call wrapped in try-catch.
            // This ensures that if one fails (e.g. already exists), the others still run,
            // and the migration doesn't crash.

            try {
                Schema::table('projects', function (Blueprint $table) {
                    $table->index(['lat', 'lng'], 'projects_lat_lng_index');
                });
            } catch (\Throwable $e) {
                // Index likely exists
            }

            try {
                Schema::table('projects', function (Blueprint $table) {
                    $table->index('city_id', 'projects_city_id_index');
                });
            } catch (\Throwable $e) {
                // Index likely exists
            }

            try {
                Schema::table('projects', function (Blueprint $table) {
                    $table->index('district_id', 'projects_district_id_index');
                });
            } catch (\Throwable $e) {
                // Index likely exists
            }
        }

        // 2) locations (countries, regions, cities, districts): Spatial queries
        // SKIPPED: MySQL requires columns to be NOT NULL for SPATIAL indexes.
        // The 'boundary' column is nullable in these tables (created by 2025_12_05_000000_add_boundary_to_locations).
        // Until we can guarantee non-null values and alter the column to NOT NULL, we cannot add this index.
        /*
        $locationTables = ['countries', 'regions', 'cities', 'districts'];
        foreach ($locationTables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'boundary')) {
                try {
                    Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                        $table->spatialIndex('boundary', $tableName . '_boundary_spatial_index');
                    });
                } catch (\Throwable $e) {}
            }
        }
        */

        // 3) units table: Combined price and area filters
        if (Schema::hasTable('units')) {
            try {
                Schema::table('units', function (Blueprint $table) {
                    if (Schema::hasColumn('units', 'built_up_area')) {
                        $table->index(['price', 'built_up_area'], 'units_price_built_up_area_index');
                    } elseif (Schema::hasColumn('units', 'area')) {
                        $table->index(['price', 'area'], 'units_price_area_index');
                    }
                });
            } catch (\Throwable $e) {
                // Index likely exists
            }
        }

        // 4) developers table: Active developers ordered by name
        if (Schema::hasTable('developers')) {
            try {
                Schema::table('developers', function (Blueprint $table) {
                    $table->index(['is_active', 'name'], 'developers_active_name_index');
                });
            } catch (\Throwable $e) {
                // Index likely exists
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1) projects table
        if (Schema::hasTable('projects')) {
            try {
                Schema::table('projects', function (Blueprint $table) {
                    $table->dropIndex('projects_lat_lng_index');
                });
            } catch (\Throwable $e) {}

            try {
                Schema::table('projects', function (Blueprint $table) {
                    $table->dropIndex('projects_city_id_index');
                });
            } catch (\Throwable $e) {}

            try {
                Schema::table('projects', function (Blueprint $table) {
                    $table->dropIndex('projects_district_id_index');
                });
            } catch (\Throwable $e) {}
        }

        // 2) locations
        // We skipped adding them, but in case they exist from manual adding:
        $locationTables = ['countries', 'regions', 'cities', 'districts'];
        foreach ($locationTables as $tableName) {
            if (Schema::hasTable($tableName)) {
                try {
                    Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                        $table->dropSpatialIndex($tableName . '_boundary_spatial_index');
                    });
                } catch (\Throwable $e) {}
            }
        }

        // 3) units table
        if (Schema::hasTable('units')) {
            try {
                Schema::table('units', function (Blueprint $table) {
                    $table->dropIndex('units_price_built_up_area_index');
                });
            } catch (\Throwable $e) {}

            try {
                Schema::table('units', function (Blueprint $table) {
                    $table->dropIndex('units_price_area_index');
                });
            } catch (\Throwable $e) {}
        }

        // 4) developers table
        if (Schema::hasTable('developers')) {
            try {
                Schema::table('developers', function (Blueprint $table) {
                    $table->dropIndex('developers_active_name_index');
                });
            } catch (\Throwable $e) {}
        }
    }
};
