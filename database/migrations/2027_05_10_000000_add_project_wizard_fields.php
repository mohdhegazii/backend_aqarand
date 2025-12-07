<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'launch_date')) {
                $table->date('launch_date')->nullable()->after('sales_launch_date');
            }
            if (!Schema::hasColumn('projects', 'description_short_ar')) {
                $table->text('description_short_ar')->nullable()->after('description_ar');
            }
            if (!Schema::hasColumn('projects', 'description_short_en')) {
                $table->text('description_short_en')->nullable()->after('description_short_ar');
            }
            if (!Schema::hasColumn('projects', 'financial_summary_ar')) {
                $table->text('financial_summary_ar')->nullable()->after('description_short_en');
            }
            if (!Schema::hasColumn('projects', 'financial_summary_en')) {
                $table->text('financial_summary_en')->nullable()->after('financial_summary_ar');
            }
            if (!Schema::hasColumn('projects', 'payment_profiles')) {
                $table->json('payment_profiles')->nullable()->after('financial_summary_en');
            }
            if (!Schema::hasColumn('projects', 'phases')) {
                $table->json('phases')->nullable()->after('payment_profiles');
            }
            if (!Schema::hasColumn('projects', 'gallery_images')) {
                $table->json('gallery_images')->nullable()->after('gallery');
            }
            if (!Schema::hasColumn('projects', 'master_plan_image')) {
                $table->string('master_plan_image')->nullable()->after('gallery_images');
            }
            if (!Schema::hasColumn('projects', 'brochure_file_path')) {
                $table->string('brochure_file_path')->nullable()->after('master_plan_image');
            }
            if (!Schema::hasColumn('projects', 'video_urls')) {
                $table->json('video_urls')->nullable()->after('video_url');
            }
            if (!Schema::hasColumn('projects', 'map_lat')) {
                $table->decimal('map_lat', 10, 7)->nullable()->after('lat');
            }
            if (!Schema::hasColumn('projects', 'map_lng')) {
                $table->decimal('map_lng', 10, 7)->nullable()->after('map_lat');
            }
            if (!Schema::hasColumn('projects', 'map_zoom')) {
                $table->unsignedInteger('map_zoom')->nullable()->after('map_lng');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            foreach ([
                'launch_date',
                'description_short_ar',
                'description_short_en',
                'financial_summary_ar',
                'financial_summary_en',
                'payment_profiles',
                'phases',
                'gallery_images',
                'master_plan_image',
                'brochure_file_path',
                'video_urls',
                'map_lat',
                'map_lng',
                'map_zoom',
            ] as $column) {
                if (Schema::hasColumn('projects', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
