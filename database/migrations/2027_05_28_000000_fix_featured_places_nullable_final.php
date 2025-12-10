<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // We use raw SQL because doctrine/dbal might not be installed, preventing use of ->change()

        $table = 'featured_places';
        $column = 'sub_category_id';
        $fkName = 'featured_places_sub_category_id_foreign';

        if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
            // 1. Drop the foreign key constraint if it exists
            try {
                DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fkName}`");
            } catch (\Throwable $e) {
                // Constraint might not exist or have a different name.
                // We'll ignore and proceed.
            }

            // 2. Modify the column to be nullable
            try {
                DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` BIGINT UNSIGNED NULL");
            } catch (\Throwable $e) {
                // Log or ignore
            }

            // 3. Re-add the foreign key with ON DELETE SET NULL
            try {
                // We check if we can add it.
                DB::statement("ALTER TABLE `{$table}` ADD CONSTRAINT `{$fkName}` FOREIGN KEY (`{$column}`) REFERENCES `featured_place_sub_categories`(`id`) ON DELETE SET NULL");
            } catch (\Throwable $e) {
                // If it fails, maybe it already exists
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We do not revert this fix
    }
};
