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
        $tables = [
            'countries', 'regions', 'cities', 'districts',
            'property_types', 'unit_types', 'amenities',
            'segments', 'categories'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    if (!Schema::hasColumn($table->getTable(), 'is_active')) {
                        $table->boolean('is_active')->default(true);
                    }
                });
            }
        }

        $imageTables = ['property_types', 'unit_types', 'amenities', 'categories', 'segments'];

        foreach ($imageTables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    if (!Schema::hasColumn($table->getTable(), 'image_path')) {
                        $table->string('image_path')->nullable();
                    }
                    if (!Schema::hasColumn($table->getTable(), 'image_url')) {
                        $table->string('image_url')->nullable();
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'countries', 'regions', 'cities', 'districts',
            'property_types', 'unit_types', 'amenities',
            'segments', 'categories'
        ];

        foreach ($tables as $table) {
             if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    if (Schema::hasColumn($table->getTable(), 'is_active')) {
                        $table->dropColumn('is_active');
                    }
                });
             }
        }

         $imageTables = ['property_types', 'unit_types', 'amenities', 'categories', 'segments'];

        foreach ($imageTables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                     if (Schema::hasColumn($table->getTable(), 'image_path')) {
                        $table->dropColumn('image_path');
                    }
                    if (Schema::hasColumn($table->getTable(), 'image_url')) {
                        $table->dropColumn('image_url');
                    }
                });
            }
        }
    }
};
