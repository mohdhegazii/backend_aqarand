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
        // 1. Remove relation from categories
        Schema::table('categories', function (Blueprint $table) {
            // Drop foreign key if it exists
            // We use the standard naming convention: categories_segment_id_foreign
            $table->dropForeign(['segment_id']);
            $table->dropColumn('segment_id');
        });

        // 2. Drop segments table
        Schema::dropIfExists('segments');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Re-create segments table
        if (!Schema::hasTable('segments')) {
            Schema::create('segments', function (Blueprint $table) {
                $table->id();
                $table->string('name_en');
                $table->string('name_ar');
                $table->string('slug')->unique();
                $table->string('image_path')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Add column back to categories
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'segment_id')) {
                $table->foreignId('segment_id')->nullable()->constrained('segments')->onDelete('cascade');
            }
        });
    }
};
