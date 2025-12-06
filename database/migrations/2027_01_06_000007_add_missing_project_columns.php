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
            // Fix missing legacy columns that might not exist in base migrations
            if (!Schema::hasColumn('projects', 'gallery')) {
                $table->json('gallery')->nullable();
            }
            if (!Schema::hasColumn('projects', 'hero_image_url')) {
                $table->string('hero_image_url', 255)->nullable();
            }
            if (!Schema::hasColumn('projects', 'brochure_url')) {
                $table->string('brochure_url', 255)->nullable();
            }

            // Add the columns intended by this migration
            if (!Schema::hasColumn('projects', 'map_polygon')) {
                $table->json('map_polygon')->nullable()->after('lng');
            }
            if (!Schema::hasColumn('projects', 'video_url')) {
                if (Schema::hasColumn('projects', 'gallery')) {
                    $table->string('video_url', 255)->nullable()->after('gallery');
                } else {
                    $table->string('video_url', 255)->nullable();
                }
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
            // We usually don't drop the 'fix' columns (gallery/hero/brochure) here
            // as they might be needed, or were supposed to be there.
        });
    }
};
