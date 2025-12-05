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
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'main_keyword_en')) {
                $table->string('main_keyword_en', 255)->nullable();
            }
            if (!Schema::hasColumn('projects', 'main_keyword_ar')) {
                $table->string('main_keyword_ar', 255)->nullable();
            }
            if (!Schema::hasColumn('projects', 'secondary_keywords_en')) {
                $table->json('secondary_keywords_en')->nullable();
            }
            if (!Schema::hasColumn('projects', 'secondary_keywords_ar')) {
                $table->json('secondary_keywords_ar')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'main_keyword_en',
                'main_keyword_ar',
                'secondary_keywords_en',
                'secondary_keywords_ar',
            ]);
        });
    }
};
