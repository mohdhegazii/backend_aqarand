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
        if (!Schema::hasTable('blog_posts')) {
            Schema::create('blog_posts', function (Blueprint $table) {
                $table->id();
                $table->string('title_en')->nullable();
                $table->string('title_ar')->nullable();
                $table->string('slug_en')->unique()->nullable();
                $table->string('slug_ar')->unique()->nullable();
                $table->longText('content_en')->nullable();
                $table->longText('content_ar')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
