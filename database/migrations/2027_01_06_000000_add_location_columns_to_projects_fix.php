<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Add location columns if they don't exist
            if (!Schema::hasColumn('projects', 'country_id')) {
                $table->foreignId('country_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('projects', 'region_id')) {
                $table->foreignId('region_id')->nullable()->after('country_id');
            }
            if (!Schema::hasColumn('projects', 'city_id')) {
                $table->foreignId('city_id')->nullable()->after('region_id');
            }
            if (!Schema::hasColumn('projects', 'district_id')) {
                $table->foreignId('district_id')->nullable()->after('city_id');
            }

            // Add coordinates if they don't exist
            if (!Schema::hasColumn('projects', 'lat')) {
                $table->decimal('lat', 10, 7)->nullable()->after('district_id');
            }
            if (!Schema::hasColumn('projects', 'lng')) {
                $table->decimal('lng', 10, 7)->nullable()->after('lat');
            }

            // Ensure seo_slug_ar exists
            if (!Schema::hasColumn('projects', 'seo_slug_ar')) {
                $table->string('seo_slug_ar')->nullable()->after('seo_slug_en');
            }

            // Ensure main_keyword columns exist
             if (!Schema::hasColumn('projects', 'main_keyword_en')) {
                $table->string('main_keyword_en')->nullable();
            }
            if (!Schema::hasColumn('projects', 'main_keyword_ar')) {
                $table->string('main_keyword_ar')->nullable();
            }
             if (!Schema::hasColumn('projects', 'secondary_keywords_en')) {
                $table->json('secondary_keywords_en')->nullable();
            }
            if (!Schema::hasColumn('projects', 'secondary_keywords_ar')) {
                $table->json('secondary_keywords_ar')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // We usually don't drop these in a fix migration to avoid data loss during rollbacks of other things,
            // but for completeness:
            //$table->dropColumn(['country_id', 'region_id', 'city_id', 'district_id', 'lat', 'lng']);
        });
    }
};
