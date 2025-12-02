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
        Schema::table('amenities', function (Blueprint $table) {
            $table->unsignedBigInteger('amenity_category_id')->nullable()->after('category_id');
            $table->foreign('amenity_category_id')->references('id')->on('amenity_categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('amenities', function (Blueprint $table) {
            $table->dropForeign(['amenity_category_id']);
            $table->dropColumn('amenity_category_id');
        });
    }
};
