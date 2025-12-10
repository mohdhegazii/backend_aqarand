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
                // MySQL requires dropping the foreign key before altering nullability
                $table->dropForeign(['sub_category_id']);

                $table->unsignedBigInteger('sub_category_id')->nullable()->default(null)->change();

                // Re-add the constraint with a null-on-delete strategy
                $table->foreign('sub_category_id')
                    ->references('id')
                    ->on('featured_place_sub_categories')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('featured_places', function (Blueprint $table) {
            if (Schema::hasColumn('featured_places', 'sub_category_id')) {
                $table->dropForeign(['sub_category_id']);

                $table->unsignedBigInteger('sub_category_id')->nullable(false)->default(null)->change();

                // Restore the original cascade behavior
                $table->foreign('sub_category_id')
                    ->references('id')
                    ->on('featured_place_sub_categories')
                    ->onDelete('cascade');
            }
        });
    }
};
