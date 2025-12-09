<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1) projects table: Map viewport and location filters
        if (Schema::hasTable('projects')) {
            Schema::table('projects', function (Blueprint $table) {
                // Check if indexes exist before adding to ensure idempotency
                // We use a try-catch for adding indexes as Schema::hasIndex is not consistently reliable across all drivers/versions for checking specific index names without table prefix issues.

                try {
                    $table->index(['lat', 'lng'], 'projects_lat_lng_index');
                } catch (\Throwable $e) {
                    // Index likely exists
                }

                try {
                    $table->index('city_id', 'projects_city_id_index');
                } catch (\Throwable $e) {
                    // Index likely exists
                }

                try {
                    $table->index('district_id', 'projects_district_id_index');
                } catch (\Throwable $e) {
                    // Index likely exists
                }
            });
        }

        // 2) locations (countries, regions, cities, districts): Spatial queries
        // SKIPPED: MySQL requires columns to be NOT NULL for SPATIAL indexes.
        // The 'boundary' column is nullable in these tables (created by 2025_12_05_000000_add_boundary_to_locations).
        // Until we can guarantee non-null values and alter the column to NOT NULL, we cannot add this index.
        /*
        $locationTables = ['countries', 'regions', 'cities', 'districts'];
        foreach ($locationTables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'boundary')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->spatialIndex('boundary', $tableName . '_boundary_spatial_index');
                });
            }
        }
        */

        // 3) units table: Combined price and area filters
        if (Schema::hasTable('units')) {
            Schema::table('units', function (Blueprint $table) {
                try {
                    if (Schema::hasColumn('units', 'built_up_area')) {
                        $table->index(['price', 'built_up_area'], 'units_price_built_up_area_index');
                    } elseif (Schema::hasColumn('units', 'area')) {
                        $table->index(['price', 'area'], 'units_price_area_index');
                    }
                } catch (\Throwable $e) {
                    // Index likely exists
                }
            });
        }

        // 4) developers table: Active developers ordered by name
        if (Schema::hasTable('developers')) {
            Schema::table('developers', function (Blueprint $table) {
                try {
                    $table->index(['is_active', 'name'], 'developers_active_name_index');
                } catch (\Throwable $e) {
                    // Index likely exists
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1) projects table
        if (Schema::hasTable('projects')) {
            Schema::table('projects', function (Blueprint $table) {
                try { $table->dropIndex('projects_lat_lng_index'); } catch (\Throwable $e) {}
                try { $table->dropIndex('projects_city_id_index'); } catch (\Throwable $e) {}
                try { $table->dropIndex('projects_district_id_index'); } catch (\Throwable $e) {}
            });
        }

        // 2) locations
        // We skipped adding them, but in case they exist from manual adding:
        $locationTables = ['countries', 'regions', 'cities', 'districts'];
        foreach ($locationTables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'boundary')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    try {
                        $table->dropSpatialIndex($tableName . '_boundary_spatial_index');
                    } catch (\Throwable $e) {}
                });
            }
        }

        // 3) units table
        if (Schema::hasTable('units')) {
            Schema::table('units', function (Blueprint $table) {
                try { $table->dropIndex('units_price_built_up_area_index'); } catch (\Throwable $e) {}
                try { $table->dropIndex('units_price_area_index'); } catch (\Throwable $e) {}
            });
        }

        // 4) developers table
        if (Schema::hasTable('developers')) {
            Schema::table('developers', function (Blueprint $table) {
                try { $table->dropIndex('developers_active_name_index'); } catch (\Throwable $e) {}
            });
        }
    }
};
