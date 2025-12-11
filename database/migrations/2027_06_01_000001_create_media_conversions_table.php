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
        if (!Schema::hasTable('media_conversions')) {
            Schema::create('media_conversions', function (Blueprint $table) {
                $table->id(); // bigIncrements
                $table->unsignedBigInteger('media_file_id')->index();
                $table->string('conversion_name', 50); // original, thumb, medium, large
                $table->string('disk', 50);
                $table->string('path', 255);
                $table->unsignedInteger('size_bytes');
                $table->integer('width')->nullable();
                $table->integer('height')->nullable();
                $table->timestamps();

                $table->foreign('media_file_id')
                      ->references('id')
                      ->on('media_files')
                      ->onDelete('cascade');

                $table->unique(['media_file_id', 'conversion_name']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_conversions');
    }
};
