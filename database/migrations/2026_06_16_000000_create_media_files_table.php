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
        if (!Schema::hasTable('media_files')) {
            Schema::create('media_files', function (Blueprint $table) {
                $table->id();
                $table->string('disk', 50)->default('public');
                $table->string('path', 255);
                $table->string('original_filename', 255)->nullable();
                $table->string('mime_type', 100)->nullable();
                $table->string('extension', 10)->nullable();
                $table->unsignedBigInteger('size_bytes')->nullable();
                $table->integer('width')->nullable();
                $table->integer('height')->nullable();

                // Context
                $table->string('context_type', 50)->nullable()->index(); // e.g. "project", "unit", "listing", "blog"
                $table->unsignedBigInteger('context_id')->nullable()->index();

                // Location hierarchy
                $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
                $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
                $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
                $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
                $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();

                // Logic fields
                $table->boolean('is_primary')->default(false);
                $table->enum('variant_role', ['original', 'webp', 'avif', 'thumbnail', 'watermarked'])->default('original');
                $table->foreignId('variant_of_id')->nullable()->constrained('media_files')->cascadeOnDelete();

                // SEO / Localization
                $table->string('alt_en', 255)->nullable();
                $table->string('alt_ar', 255)->nullable();
                $table->string('title_en', 255)->nullable();
                $table->string('title_ar', 255)->nullable();
                $table->json('seo_keywords_en')->nullable();
                $table->json('seo_keywords_ar')->nullable();

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_files');
    }
};
