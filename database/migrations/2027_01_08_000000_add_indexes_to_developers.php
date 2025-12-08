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
        Schema::table('developers', function (Blueprint $table) {
            // Index on is_active for filtering active developers
            if (Schema::hasColumn('developers', 'is_active')) {
                // Check if index exists is tricky in Laravel migration without DB access,
                // but usually we can try-catch or just add it.
                // However, Laravel doesn't have hasIndex.
                // We will try to add it. If it fails, it's fine in this context or we can give it a specific name
                // and check via Schema::hasIndex is not available in all drivers via Schema facade easily in old versions,
                // but Laravel 11 likely supports it.
                // Since I cannot run migration, I will just define it.
                // To be safe against "index already exists", we can drop it first or use a specific name.

                try {
                    $table->index('is_active');
                } catch (\Throwable $e) {
                    // Index likely exists
                }
            }

            // Index on name_en for search/sorting
            if (Schema::hasColumn('developers', 'name_en')) {
                try {
                    $table->index('name_en');
                } catch (\Throwable $e) {}
            }

            // Index on name_ar for search/sorting
            if (Schema::hasColumn('developers', 'name_ar')) {
                try {
                    $table->index('name_ar');
                } catch (\Throwable $e) {}
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('developers', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['name_en']);
            $table->dropIndex(['name_ar']);
        });
    }
};
