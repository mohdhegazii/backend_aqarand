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
        // Property Types
        Schema::table('property_types', function (Blueprint $table) {
            if (!Schema::hasColumn('property_types', 'icon_class')) {
                $table->string('icon_class', 120)->nullable()->after('slug');
            }
            if (!Schema::hasColumn('property_types', 'image_url')) {
                $table->string('image_url', 255)->nullable()->after('icon_class');
            }
        });

        // Unit Types
        Schema::table('unit_types', function (Blueprint $table) {
            if (!Schema::hasColumn('unit_types', 'icon_class')) {
                $table->string('icon_class', 120)->nullable()->after('description');
            }
            if (!Schema::hasColumn('unit_types', 'image_url')) {
                $table->string('image_url', 255)->nullable()->after('icon_class');
            }
        });

        // Amenities
        Schema::table('amenities', function (Blueprint $table) {
            if (!Schema::hasColumn('amenities', 'image_url')) {
                $table->string('image_url', 255)->nullable()->after('icon_class');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('property_types', function (Blueprint $table) {
            if (Schema::hasColumn('property_types', 'image_url')) {
                $table->dropColumn('image_url');
            }
            if (Schema::hasColumn('property_types', 'icon_class')) {
                $table->dropColumn('icon_class');
            }
        });

        Schema::table('unit_types', function (Blueprint $table) {
            if (Schema::hasColumn('unit_types', 'image_url')) {
                $table->dropColumn('image_url');
            }
            if (Schema::hasColumn('unit_types', 'icon_class')) {
                $table->dropColumn('icon_class');
            }
        });

        Schema::table('amenities', function (Blueprint $table) {
            if (Schema::hasColumn('amenities', 'image_url')) {
                $table->dropColumn('image_url');
            }
        });
    }
};
