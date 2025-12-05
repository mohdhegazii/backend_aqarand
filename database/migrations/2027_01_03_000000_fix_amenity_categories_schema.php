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
        Schema::table('amenity_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('amenity_categories', 'name_en')) {
                $table->string('name_en', 150)->after('id');
            }
            if (!Schema::hasColumn('amenity_categories', 'name_ar')) {
                // We attempt to place name_ar after name_en.
                // If name_en was just added, MySQL will handle the order correctly.
                $table->string('name_ar', 150)->after('name_en');
            }

            if (!Schema::hasColumn('amenity_categories', 'slug')) {
                $table->string('slug', 180)->unique()->after('name_ar');
            }

            if (!Schema::hasColumn('amenity_categories', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('slug');
            }

            if (!Schema::hasColumn('amenity_categories', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('amenity_categories', function (Blueprint $table) {
            if (Schema::hasColumn('amenity_categories', 'name_ar')) {
                $table->dropColumn('name_ar');
            }
            if (Schema::hasColumn('amenity_categories', 'name_en')) {
                $table->dropColumn('name_en');
            }
            if (Schema::hasColumn('amenity_categories', 'slug')) {
                $table->dropColumn('slug');
            }
            if (Schema::hasColumn('amenity_categories', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (Schema::hasColumn('amenity_categories', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });
    }
};
