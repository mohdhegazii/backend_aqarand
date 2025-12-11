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
        if (!Schema::hasTable('media_tags')) {
            Schema::create('media_tags', function (Blueprint $table) {
                $table->id();
                $table->string('name', 50);
                $table->string('slug', 50)->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('media_file_tag')) {
            Schema::create('media_file_tag', function (Blueprint $table) {
                $table->unsignedBigInteger('media_file_id');
                $table->unsignedBigInteger('media_tag_id');

                $table->primary(['media_file_id', 'media_tag_id']);

                $table->foreign('media_file_id')
                      ->references('id')
                      ->on('media_files')
                      ->onDelete('cascade');

                $table->foreign('media_tag_id')
                      ->references('id')
                      ->on('media_tags')
                      ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_file_tag');
        Schema::dropIfExists('media_tags');
    }
};
