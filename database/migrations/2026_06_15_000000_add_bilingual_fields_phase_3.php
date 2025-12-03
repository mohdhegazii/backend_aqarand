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
        // Projects
        if (Schema::hasTable('projects')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->string('name_en', 200)->nullable()->after('name');
                $table->string('name_ar', 200)->nullable()->after('name_en');

                $table->string('tagline_en', 255)->nullable()->after('tagline');
                $table->string('tagline_ar', 255)->nullable()->after('tagline_en');

                $table->text('description_en')->nullable()->after('description_long');
                $table->text('description_ar')->nullable()->after('description_en');

                $table->string('address_en', 255)->nullable()->after('address_text');
                $table->string('address_ar', 255)->nullable()->after('address_en');

                $table->string('seo_slug_en', 220)->nullable()->unique()->after('slug');
                $table->string('seo_slug_ar', 220)->nullable()->unique()->after('seo_slug_en');

                $table->string('meta_title_en', 255)->nullable()->after('meta_title');
                $table->string('meta_title_ar', 255)->nullable()->after('meta_title_en');

                $table->string('meta_description_en', 255)->nullable()->after('meta_description');
                $table->string('meta_description_ar', 255)->nullable()->after('meta_description_en');
            });
        }

        // Property Models
        if (Schema::hasTable('property_models')) {
            Schema::table('property_models', function (Blueprint $table) {
                $table->string('name_en', 200)->nullable()->after('name');
                $table->string('name_ar', 200)->nullable()->after('name_en');

                $table->text('description_en')->nullable()->after('description');
                $table->text('description_ar')->nullable()->after('description_en');

                $table->string('seo_slug_en', 220)->nullable()->unique()->after('seo_slug');
                $table->string('seo_slug_ar', 220)->nullable()->unique()->after('seo_slug_en');

                $table->string('meta_title_en', 255)->nullable()->after('meta_title');
                $table->string('meta_title_ar', 255)->nullable()->after('meta_title_en');

                $table->string('meta_description_en', 255)->nullable()->after('meta_description');
                $table->string('meta_description_ar', 255)->nullable()->after('meta_description_en');
            });
        }

        // Units
        if (Schema::hasTable('units')) {
            Schema::table('units', function (Blueprint $table) {
                // Units don't have a main 'title' in schema, just unit_number, etc.
                // But prompt asks for title_en, title_ar.
                $table->string('title_en', 255)->nullable()->after('unit_number');
                $table->string('title_ar', 255)->nullable()->after('title_en');

                $table->string('meta_title_en', 255)->nullable()->after('media');
                $table->string('meta_title_ar', 255)->nullable()->after('meta_title_en');

                $table->string('meta_description_en', 255)->nullable()->after('meta_title_ar');
                $table->string('meta_description_ar', 255)->nullable()->after('meta_description_en');
            });
        }

        // Listings
        if (Schema::hasTable('listings')) {
            Schema::table('listings', function (Blueprint $table) {
                $table->string('title_en', 255)->nullable()->after('title');
                $table->string('title_ar', 255)->nullable()->after('title_en');

                $table->string('slug_en', 255)->nullable()->unique()->after('slug');
                $table->string('slug_ar', 255)->nullable()->unique()->after('slug_en');

                $table->string('seo_title_en', 255)->nullable()->after('seo_title');
                $table->string('seo_title_ar', 255)->nullable()->after('seo_title_en');

                $table->string('seo_description_en', 255)->nullable()->after('seo_description');
                $table->string('seo_description_ar', 255)->nullable()->after('seo_description_en');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('projects')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn([
                    'name_en', 'name_ar',
                    'tagline_en', 'tagline_ar',
                    'description_en', 'description_ar',
                    'address_en', 'address_ar',
                    'seo_slug_en', 'seo_slug_ar',
                    'meta_title_en', 'meta_title_ar',
                    'meta_description_en', 'meta_description_ar'
                ]);
            });
        }

        if (Schema::hasTable('property_models')) {
            Schema::table('property_models', function (Blueprint $table) {
                $table->dropColumn([
                    'name_en', 'name_ar',
                    'description_en', 'description_ar',
                    'seo_slug_en', 'seo_slug_ar',
                    'meta_title_en', 'meta_title_ar',
                    'meta_description_en', 'meta_description_ar'
                ]);
            });
        }

        if (Schema::hasTable('units')) {
            Schema::table('units', function (Blueprint $table) {
                $table->dropColumn([
                    'title_en', 'title_ar',
                    'meta_title_en', 'meta_title_ar',
                    'meta_description_en', 'meta_description_ar'
                ]);
            });
        }

        if (Schema::hasTable('listings')) {
            Schema::table('listings', function (Blueprint $table) {
                $table->dropColumn([
                    'title_en', 'title_ar',
                    'slug_en', 'slug_ar',
                    'seo_title_en', 'seo_title_ar',
                    'seo_description_en', 'seo_description_ar'
                ]);
            });
        }
    }
};
