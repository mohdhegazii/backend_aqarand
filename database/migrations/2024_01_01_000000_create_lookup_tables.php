<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('countries')) {
            Schema::create('countries', function (Blueprint $table) {
                $table->id();
                $table->string('code', 5)->unique();
                $table->string('name_en');
                $table->string('name_local')->nullable();
                $table->string('phone_code', 10)->nullable();
                $table->decimal('lat', 10, 7)->nullable();
                $table->decimal('lng', 10, 7)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('regions')) {
            Schema::create('regions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
                $table->string('name_en');
                $table->string('name_local')->nullable();
                $table->string('slug')->unique();
                $table->decimal('lat', 10, 7)->nullable();
                $table->decimal('lng', 10, 7)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('cities')) {
            Schema::create('cities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();
                $table->string('name_en');
                $table->string('name_local')->nullable();
                $table->string('slug')->unique();
                $table->decimal('lat', 10, 7)->nullable();
                $table->decimal('lng', 10, 7)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('districts')) {
            Schema::create('districts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();
                $table->string('name_en');
                $table->string('name_local')->nullable();
                $table->string('slug')->unique();
                $table->decimal('lat', 10, 7)->nullable();
                $table->decimal('lng', 10, 7)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('property_types')) {
            Schema::create('property_types', function (Blueprint $table) {
                $table->id();
                $table->string('name_en');
                $table->string('name_local')->nullable();
                $table->string('slug')->unique();
                $table->string('category')->nullable();
                $table->string('icon_class', 120)->nullable();
                $table->string('image_url', 255)->nullable();
                $table->string('image_path', 255)->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('unit_types')) {
            Schema::create('unit_types', function (Blueprint $table) {
                $table->id();
                $table->foreignId('property_type_id')->constrained('property_types')->cascadeOnDelete();
                $table->string('name');
                $table->string('code', 50)->nullable();
                $table->text('description')->nullable();
                $table->string('icon_class', 120)->nullable();
                $table->string('image_url', 255)->nullable();
                $table->string('image_path', 255)->nullable();
                $table->boolean('is_core_type')->default(false);
                $table->boolean('requires_land_area')->default(false);
                $table->boolean('requires_built_up_area')->default(true);
                $table->boolean('requires_garden_area')->default(false);
                $table->boolean('requires_roof_area')->default(false);
                $table->boolean('requires_indoor_area')->default(false);
                $table->boolean('requires_outdoor_area')->default(false);
                $table->json('additional_rules')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->nullable();
                $table->timestamps();
            });
        }

        // --- ده الجدول اللي كان ناقص وضفته ليك هنا ---
        if (!Schema::hasTable('amenity_categories')) {
            Schema::create('amenity_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name_en');
                $table->string('name_local')->nullable();
                $table->string('slug')->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
        // -----------------------------------------

        if (!Schema::hasTable('amenities')) {
            Schema::create('amenities', function (Blueprint $table) {
                $table->id();
                // دلوقتي الجدول موجود وهيقدر يربط عليه عادي
                $table->foreignId('amenity_category_id')->nullable()->constrained('amenity_categories')->nullOnDelete();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->string('name_en');
                $table->string('name_local')->nullable();
                $table->string('slug')->unique();
                $table->string('icon_class', 120)->nullable();
                $table->string('image_url', 255)->nullable();
                $table->string('image_path', 255)->nullable();
                $table->string('amenity_type')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('amenities');
        Schema::dropIfExists('amenity_categories'); // ضفت أمر الحذف هنا كمان
        Schema::dropIfExists('unit_types');
        Schema::dropIfExists('property_types');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('regions');
        Schema::dropIfExists('countries');
    }
};