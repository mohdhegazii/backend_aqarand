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
            if (Schema::hasColumn('featured_places', 'sub_category_id')) {
                // MySQL often requires dropping the foreign key before modifying the column
                // We use a try-catch block to handle cases where the FK might not exist or has a non-standard name
                try {
                    $table->dropForeign(['sub_category_id']);
                } catch (\Throwable $e) {
                    // Continue even if FK drop fails (e.g. if it doesn't exist)
                }

                // Modify the column to be nullable
                $table->foreignId('sub_category_id')->nullable()->change();

                // Re-add the foreign key constraint with nullOnDelete behavior
                try {
                    $table->foreign('sub_category_id')
                        ->references('id')
                        ->on('featured_place_sub_categories')
                        ->nullOnDelete();
                } catch (\Throwable $e) {
                    // Continue if FK addition fails (e.g. constraint already exists)
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We do not revert this change as it fixes a critical bug.
        // Making it NOT NULL again would cause data loss for records with null sub_category_id.
    }
};
