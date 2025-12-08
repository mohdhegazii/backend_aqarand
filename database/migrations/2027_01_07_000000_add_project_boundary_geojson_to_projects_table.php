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
        if (!Schema::hasColumn('projects', 'project_boundary_geojson')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->json('project_boundary_geojson')->nullable()->after('map_polygon');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('projects', 'project_boundary_geojson')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('project_boundary_geojson');
            });
        }
    }
};
