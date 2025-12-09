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
        // 1. Main Categories
        if (!Schema::hasTable('featured_place_main_categories')) {
            Schema::create('featured_place_main_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name_ar');
                $table->string('name_en');
                $table->string('slug')->unique();
                $table->string('icon_name')->nullable();
                $table->string('pin_icon')->nullable();
                $table->text('description_ar')->nullable();
                $table->text('description_en')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Sub Categories
        if (!Schema::hasTable('featured_place_sub_categories')) {
            Schema::create('featured_place_sub_categories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('main_category_id')->constrained('featured_place_main_categories')->onDelete('cascade');
                $table->string('name_ar');
                $table->string('name_en');
                $table->string('slug'); // Unique within main category logic handled in code, or composite unique index
                $table->text('description_ar')->nullable();
                $table->text('description_en')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['main_category_id', 'slug'], 'sub_cat_slug_unique');
            });
        }

        // 3. Featured Places
        if (!Schema::hasTable('featured_places')) {
            Schema::create('featured_places', function (Blueprint $table) {
                $table->id();
                $table->foreignId('main_category_id')->constrained('featured_place_main_categories')->onDelete('cascade');
                $table->foreignId('sub_category_id')->constrained('featured_place_sub_categories')->onDelete('cascade');

                // Location Hierarchy
                $table->foreignId('country_id')->constrained('countries')->onDelete('cascade');
                $table->foreignId('region_id')->constrained('regions')->onDelete('cascade');
                $table->foreignId('city_id')->constrained('cities')->onDelete('cascade');
                $table->foreignId('district_id')->nullable()->constrained('districts')->onDelete('set null');

                $table->string('name_ar');
                $table->string('name_en');
                $table->text('description_ar')->nullable();
                $table->text('description_en')->nullable();

                // Pin / Icon override
                $table->string('pin_icon')->nullable();

                // Geometry
                $table->decimal('point_lat', 10, 7)->nullable();
                $table->decimal('point_lng', 10, 7)->nullable();
                $table->json('polygon_geojson')->nullable();

                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('featured_places');
        Schema::dropIfExists('featured_place_sub_categories');
        Schema::dropIfExists('featured_place_main_categories');
    }
};
