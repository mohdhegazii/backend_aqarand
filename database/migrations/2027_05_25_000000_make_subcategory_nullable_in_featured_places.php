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
        Schema::table('featured_places', function (Blueprint $table) {
            // Check if column exists first to be safe, though modify requires it
            if (Schema::hasColumn('featured_places', 'sub_category_id')) {
                $table->foreignId('sub_category_id')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('featured_places', function (Blueprint $table) {
             // We can't easily revert to NOT NULL without ensuring data integrity
             // So we'll skip strictly enforcing it back or just try
             $table->foreignId('sub_category_id')->nullable(false)->change();
        });
    }
};
