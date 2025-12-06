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
        if (!Schema::hasTable('project_amenity')) {
            Schema::create('project_amenity', function (Blueprint $table) {
                // We use bigInteger just to be safe if IDs are big, but foreignId handles it.
                // Assuming project_id and amenity_id match the types of projects.id and amenities.id
                $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
                $table->foreignId('amenity_id')->constrained('amenities')->cascadeOnDelete();

                // Add composite primary key or unique index
                $table->primary(['project_id', 'amenity_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_amenity');
    }
};
