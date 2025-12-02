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
        // Add lat/lng to locations
        Schema::table('countries', function (Blueprint $table) {
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
        });

        Schema::table('regions', function (Blueprint $table) {
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
        });

        Schema::table('districts', function (Blueprint $table) {
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
        });

        // Add icon_class and image_url to property_types
        Schema::table('property_types', function (Blueprint $table) {
            if (!Schema::hasColumn('property_types', 'icon_class')) {
                $table->string('icon_class', 120)->nullable();
            }
            if (!Schema::hasColumn('property_types', 'image_url')) {
                $table->string('image_url', 255)->nullable();
            }
        });

        // Add icon_class and image_url to unit_types
        Schema::table('unit_types', function (Blueprint $table) {
            if (!Schema::hasColumn('unit_types', 'icon_class')) {
                $table->string('icon_class', 120)->nullable();
            }
            if (!Schema::hasColumn('unit_types', 'image_url')) {
                $table->string('image_url', 255)->nullable();
            }
        });

        // Add image_url to amenities
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
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng']);
        });

        Schema::table('regions', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng']);
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng']);
        });

        Schema::table('districts', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng']);
        });

        Schema::table('property_types', function (Blueprint $table) {
            $table->dropColumn(['icon_class', 'image_url']);
        });

        Schema::table('unit_types', function (Blueprint $table) {
            $table->dropColumn(['icon_class', 'image_url']);
        });

        Schema::table('amenities', function (Blueprint $table) {
            $table->dropColumn(['image_url']);
        });
    }
};
