<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Remove relation from categories
        if (Schema::hasTable('categories')) {
            // Drop foreign key in its own block with try-catch
            try {
                Schema::table('categories', function (Blueprint $table) {
                    $table->dropForeign(['segment_id']);
                });
            } catch (QueryException $e) {
                 // 1091 = Can't DROP 'x'; check that column/key exists
                 // We ignore specifically if it says it doesn't exist.
                 if ($e->getCode() !== '42000' && !str_contains($e->getMessage(), 'check that it exists')) {
                    throw $e;
                 }
            }

            // Drop column if exists
            if (Schema::hasColumn('categories', 'segment_id')) {
                Schema::table('categories', function (Blueprint $table) {
                    $table->dropColumn('segment_id');
                });
            }
        }

        // 2. Drop segments table
        Schema::dropIfExists('segments');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left blank. The segments table is permanently removed.
    }
};
