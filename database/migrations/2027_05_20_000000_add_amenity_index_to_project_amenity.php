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
        try {
            Schema::table('project_amenity', function (Blueprint $table) {
                // We add the index. If it exists, it might throw, so we catch.
                // However, Schema::hasIndex is preferable if reliable, but try-catch is robust against drift.
                $table->index('amenity_id', 'project_amenity_amenity_id_index');
            });
        } catch (\Throwable $e) {
            // Ignore "Duplicate key name" error (Code 1061 in MySQL)
            if (!str_contains($e->getMessage(), 'Duplicate key name') && !str_contains($e->getMessage(), 'already exists')) {
                throw $e;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_amenity', function (Blueprint $table) {
            $table->dropIndex('project_amenity_amenity_id_index');
        });
    }
};
