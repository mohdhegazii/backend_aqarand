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
        if (!Schema::hasTable('media_links')) {
            Schema::create('media_links', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('media_file_id')->index();
                $table->string('model_type', 100);
                $table->unsignedBigInteger('model_id');
                $table->string('role', 50); // e.g. featured, gallery
                $table->integer('ordering')->default(0);
                $table->timestamps();

                $table->index(['model_type', 'model_id']);

                $table->foreign('media_file_id')
                      ->references('id')
                      ->on('media_files')
                      ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_links');
    }
};
