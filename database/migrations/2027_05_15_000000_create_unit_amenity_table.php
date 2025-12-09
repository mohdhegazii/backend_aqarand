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
        if (!Schema::hasTable('unit_amenity')) {
            Schema::create('unit_amenity', function (Blueprint $table) {
                $table->id();
                $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
                $table->foreignId('amenity_id')->constrained('amenities')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['unit_id', 'amenity_id']);
                $table->index('amenity_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_amenity');
    }
};
