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
        if (Schema::hasTable('amenity_categories')) {
            Schema::table('amenity_categories', function (Blueprint $table) {
                if (!Schema::hasColumn('amenity_categories', 'sort_order')) {
                    $table->integer('sort_order')->default(0)->after('is_active');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('amenity_categories')) {
            Schema::table('amenity_categories', function (Blueprint $table) {
                if (Schema::hasColumn('amenity_categories', 'sort_order')) {
                    $table->dropColumn('sort_order');
                }
            });
        }
    }
};
