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
        if (!Schema::hasTable('property_models')) {
            Schema::create('property_models', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
                $table->foreignId('unit_type_id')->constrained('unit_types')->restrictOnDelete();

                // Base fields
                $table->string('name', 200);
                $table->string('name_en')->nullable();
                $table->string('name_ar')->nullable();

                $table->string('code', 50)->nullable();

                $table->text('description')->nullable();
                $table->text('description_en')->nullable();
                $table->text('description_ar')->nullable();

                // Specs
                $table->unsignedTinyInteger('bedrooms')->nullable();
                $table->unsignedTinyInteger('bathrooms')->nullable();
                $table->decimal('min_bua', 10, 2)->nullable();
                $table->decimal('max_bua', 10, 2)->nullable();
                $table->decimal('min_land_area', 10, 2)->nullable();
                $table->decimal('max_land_area', 10, 2)->nullable();
                $table->decimal('min_price', 14, 2)->nullable();
                $table->decimal('max_price', 14, 2)->nullable();

                // Media
                $table->string('floorplan_2d_url')->nullable();
                $table->string('floorplan_3d_url')->nullable();
                $table->json('gallery')->nullable();

                // SEO
                $table->string('seo_slug', 220)->nullable();
                $table->string('seo_slug_en')->nullable();
                $table->string('seo_slug_ar')->nullable();

                $table->string('meta_title')->nullable();
                $table->string('meta_title_ar')->nullable();

                $table->string('meta_description')->nullable();
                $table->string('meta_description_ar')->nullable();

                $table->boolean('is_active')->default(true);
                $table->timestamps();

                // Indexes
                $table->index('project_id');
                $table->index('unit_type_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_models');
    }
};
