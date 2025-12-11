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
        if (!Schema::hasTable('media_settings')) {
            Schema::create('media_settings', function (Blueprint $table) {
                $table->id();
                $table->string('disk_default', 50)->default('s3_media_local');
                $table->string('disk_system_assets', 50)->default('local_work');
                $table->integer('image_max_size_mb')->default(2);
                $table->integer('pdf_max_size_mb')->default(10);
                $table->integer('image_quality')->default(80);
                $table->integer('image_max_width')->default(2000);
                $table->boolean('generate_webp')->default(true);
                $table->boolean('generate_thumb')->default(true);
                $table->integer('thumb_width')->default(400);
                $table->integer('medium_width')->default(800);
                $table->integer('large_width')->default(1600);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_settings');
    }
};
