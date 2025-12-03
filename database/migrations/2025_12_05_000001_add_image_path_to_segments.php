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
        if (Schema::hasTable('segments')) {
            Schema::table('segments', function (Blueprint $table) {
                if (!Schema::hasColumn('segments', 'image_path')) {
                    $table->string('image_path')->nullable()->after('slug');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('segments')) {
            Schema::table('segments', function (Blueprint $table) {
                if (Schema::hasColumn('segments', 'image_path')) {
                    $table->dropColumn('image_path');
                }
            });
        }
    }
};
