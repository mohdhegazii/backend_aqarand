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
        if (!Schema::hasTable('listings')) {
            Schema::create('listings', function (Blueprint $table) {
                // Base fields from schema.sql
                $table->id();
                $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
                $table->enum('listing_type', ['primary','resale','rental'])->default('primary');

                $table->string('title');
                $table->string('title_en')->nullable(); // Bilingual field
                $table->string('title_ar')->nullable(); // Bilingual field

                $table->string('slug')->unique();
                $table->string('slug_en')->nullable(); // Bilingual field
                $table->string('slug_ar')->nullable(); // Bilingual field

                $table->string('short_description', 500)->nullable();
                $table->enum('status', ['draft','pending','published','hidden','sold','rented','expired'])->default('draft');
                $table->boolean('is_featured')->default(0);
                $table->timestamp('published_at')->nullable();

                $table->string('seo_title')->nullable();
                $table->string('seo_title_en')->nullable(); // Bilingual field
                $table->string('seo_title_ar')->nullable(); // Bilingual field

                $table->string('seo_description')->nullable();
                $table->string('seo_description_en')->nullable(); // Bilingual field
                $table->string('seo_description_ar')->nullable(); // Bilingual field

                $table->timestamps();

                // Indexes from schema.sql
                $table->index('status');
                $table->index('listing_type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
