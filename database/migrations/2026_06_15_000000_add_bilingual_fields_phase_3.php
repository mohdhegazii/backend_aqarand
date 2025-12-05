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
        // 1. Projects
        if (Schema::hasTable('projects')) {
            Schema::table('projects', function (Blueprint $table) {
                if (!Schema::hasColumn('projects', 'name_en')) {
                    $table->string('name_en')->nullable()->after('name');
                }
                if (!Schema::hasColumn('projects', 'name_ar')) {
                    $table->string('name_ar')->nullable()->after('name_en');
                }
                if (!Schema::hasColumn('projects', 'seo_slug_en')) {
                    $table->string('seo_slug_en')->nullable()->after('slug');
                }
                if (!Schema::hasColumn('projects', 'seo_slug_ar')) {
                    $table->string('seo_slug_ar')->nullable()->after('seo_slug_en');
                }
                if (!Schema::hasColumn('projects', 'description_en')) {
                    $table->text('description_en')->nullable()->after('description_long');
                }
                if (!Schema::hasColumn('projects', 'description_ar')) {
                    $table->text('description_ar')->nullable()->after('description_en');
                }
                if (!Schema::hasColumn('projects', 'meta_title_ar')) {
                    $table->string('meta_title_ar')->nullable()->after('meta_title');
                }
                if (!Schema::hasColumn('projects', 'meta_description_ar')) {
                    $table->string('meta_description_ar')->nullable()->after('meta_description');
                }
            });
        }

        // 2. Property Models
        if (Schema::hasTable('property_models')) {
            Schema::table('property_models', function (Blueprint $table) {
                if (!Schema::hasColumn('property_models', 'name_en')) {
                    $table->string('name_en')->nullable()->after('name');
                }
                if (!Schema::hasColumn('property_models', 'name_ar')) {
                    $table->string('name_ar')->nullable()->after('name_en');
                }
                if (!Schema::hasColumn('property_models', 'seo_slug_en')) {
                    $table->string('seo_slug_en')->nullable()->after('seo_slug');
                }
                if (!Schema::hasColumn('property_models', 'seo_slug_ar')) {
                    $table->string('seo_slug_ar')->nullable()->after('seo_slug_en');
                }
                if (!Schema::hasColumn('property_models', 'description_en')) {
                    $table->text('description_en')->nullable()->after('description');
                }
                if (!Schema::hasColumn('property_models', 'description_ar')) {
                    $table->text('description_ar')->nullable()->after('description_en');
                }
                if (!Schema::hasColumn('property_models', 'meta_title_ar')) {
                    $table->string('meta_title_ar')->nullable()->after('meta_title');
                }
                if (!Schema::hasColumn('property_models', 'meta_description_ar')) {
                    $table->string('meta_description_ar')->nullable()->after('meta_description');
                }
            });
        }

        // 3. Listings
        if (Schema::hasTable('listings')) {
            Schema::table('listings', function (Blueprint $table) {
                if (!Schema::hasColumn('listings', 'title_en')) {
                    $table->string('title_en')->nullable()->after('title');
                }
                if (!Schema::hasColumn('listings', 'title_ar')) {
                    $table->string('title_ar')->nullable()->after('title_en');
                }
                if (!Schema::hasColumn('listings', 'slug_en')) {
                    $table->string('slug_en')->nullable()->after('slug');
                }
                if (!Schema::hasColumn('listings', 'slug_ar')) {
                    $table->string('slug_ar')->nullable()->after('slug_en');
                }
                if (!Schema::hasColumn('listings', 'seo_title_en')) {
                    $table->string('seo_title_en')->nullable()->after('seo_title');
                }
                if (!Schema::hasColumn('listings', 'seo_title_ar')) {
                    $table->string('seo_title_ar')->nullable()->after('seo_title_en');
                }
                if (!Schema::hasColumn('listings', 'seo_description_en')) {
                    $table->string('seo_description_en')->nullable()->after('seo_description');
                }
                if (!Schema::hasColumn('listings', 'seo_description_ar')) {
                    $table->string('seo_description_ar')->nullable()->after('seo_description_en');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Projects
        if (Schema::hasTable('projects')) {
            Schema::table('projects', function (Blueprint $table) {
                $columns = [
                    'name_en', 'name_ar',
                    'seo_slug_en', 'seo_slug_ar',
                    'description_en', 'description_ar',
                    'meta_title_ar', 'meta_description_ar'
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('projects', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        // 2. Property Models
        if (Schema::hasTable('property_models')) {
            Schema::table('property_models', function (Blueprint $table) {
                $columns = [
                    'name_en', 'name_ar',
                    'seo_slug_en', 'seo_slug_ar',
                    'description_en', 'description_ar',
                    'meta_title_ar', 'meta_description_ar'
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('property_models', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        // 3. Listings
        if (Schema::hasTable('listings')) {
            Schema::table('listings', function (Blueprint $table) {
                $columns = [
                    'title_en', 'title_ar',
                    'slug_en', 'slug_ar',
                    'seo_title_en', 'seo_title_ar',
                    'seo_description_en', 'seo_description_ar'
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('listings', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
