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
        // 1. Segments Table
        Schema::create('segments', function (Blueprint $table) {
            $table->id();
            $table->string('name_en');
            $table->string('name_ar');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Categories Table
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('segment_id')->constrained('segments')->onDelete('cascade');
            $table->string('name_en');
            $table->string('name_ar');
            $table->string('slug')->unique();
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Countries - Add is_active and lat/lng if not present
        Schema::table('countries', function (Blueprint $table) {
            if (!Schema::hasColumn('countries', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            if (!Schema::hasColumn('countries', 'lat')) {
                 $table->decimal('lat', 10, 7)->nullable();
            }
            if (!Schema::hasColumn('countries', 'lng')) {
                 $table->decimal('lng', 10, 7)->nullable();
            }
        });

        // 3.1 Locations (Regions, Cities, Districts) - Add lat/lng
        Schema::table('regions', function (Blueprint $table) {
            if (!Schema::hasColumn('regions', 'lat')) {
                $table->decimal('lat', 10, 7)->nullable();
            }
            if (!Schema::hasColumn('regions', 'lng')) {
                $table->decimal('lng', 10, 7)->nullable();
            }
        });

        Schema::table('cities', function (Blueprint $table) {
            if (!Schema::hasColumn('cities', 'lat')) {
                $table->decimal('lat', 10, 7)->nullable();
            }
            if (!Schema::hasColumn('cities', 'lng')) {
                $table->decimal('lng', 10, 7)->nullable();
            }
        });

        Schema::table('districts', function (Blueprint $table) {
            if (!Schema::hasColumn('districts', 'lat')) {
                $table->decimal('lat', 10, 7)->nullable();
            }
            if (!Schema::hasColumn('districts', 'lng')) {
                $table->decimal('lng', 10, 7)->nullable();
            }
        });

        // 4. Developers - Add bilingual fields and logo_path
        Schema::table('developers', function (Blueprint $table) {
            $table->string('name_en')->nullable();
            $table->string('name_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('logo_path')->nullable();
        });

        // 5. SEO Meta Table
        Schema::create('seo_meta', function (Blueprint $table) {
            $table->id();
            $table->morphs('seoable');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable(); // changed to text for safety
            $table->string('focus_keyphrase')->nullable();
            $table->json('meta_data')->nullable();
            $table->timestamps();
        });

        // 6. Amenities - Add category_id and image_path
        Schema::table('amenities', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            if (!Schema::hasColumn('amenities', 'image_path')) {
                $table->string('image_path')->nullable();
            }
        });

        // 7. Property Types - Add image_path
        Schema::table('property_types', function (Blueprint $table) {
            if (!Schema::hasColumn('property_types', 'image_path')) {
                $table->string('image_path')->nullable();
            }
        });

        // 8. Unit Types - Add image_path
        Schema::table('unit_types', function (Blueprint $table) {
            if (!Schema::hasColumn('unit_types', 'image_path')) {
                $table->string('image_path')->nullable();
            }
        });

        // 9. Lookup Enhancements - icon_class/image_url for property/unit types/amenities if missing
        Schema::table('property_types', function (Blueprint $table) {
            if (!Schema::hasColumn('property_types', 'icon_class')) {
                $table->string('icon_class', 120)->nullable();
            }
            if (!Schema::hasColumn('property_types', 'image_url')) {
                $table->string('image_url', 255)->nullable();
            }
        });

        Schema::table('unit_types', function (Blueprint $table) {
            if (!Schema::hasColumn('unit_types', 'icon_class')) {
                $table->string('icon_class', 120)->nullable();
            }
            if (!Schema::hasColumn('unit_types', 'image_url')) {
                $table->string('image_url', 255)->nullable();
            }
        });

        Schema::table('amenities', function (Blueprint $table) {
            if (!Schema::hasColumn('amenities', 'image_url')) {
                $table->string('image_url', 255)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unit_types', function (Blueprint $table) {
             if (Schema::hasColumn('unit_types', 'image_path')) {
                $table->dropColumn('image_path');
            }
            // Optional: drop icon_class/image_url if we want strict rollback, but usually safe to keep
        });

        Schema::table('property_types', function (Blueprint $table) {
            if (Schema::hasColumn('property_types', 'image_path')) {
                $table->dropColumn('image_path');
            }
        });

        Schema::table('amenities', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
            if (Schema::hasColumn('amenities', 'image_path')) {
                $table->dropColumn('image_path');
            }
        });

        Schema::dropIfExists('seo_meta');

        Schema::table('developers', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'name_ar', 'description_en', 'description_ar', 'logo_path']);
        });

        Schema::table('countries', function (Blueprint $table) {
            if (Schema::hasColumn('countries', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });

        Schema::dropIfExists('categories');
        Schema::dropIfExists('segments');
    }
};
