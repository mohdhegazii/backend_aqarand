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
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'map_polygon')) {
                $table->json('map_polygon')->nullable()->after('lng');
            }
            if (!Schema::hasColumn('projects', 'video_url')) {
                $table->string('video_url', 255)->nullable()->after('gallery');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'map_polygon')) {
                $table->dropColumn('map_polygon');
            }
            if (Schema::hasColumn('projects', 'video_url')) {
                $table->dropColumn('video_url');
            }
        });
    }
};
