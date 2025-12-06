<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Price Columns
            if (!Schema::hasColumn('projects', 'min_price')) {
                $table->decimal('min_price', 14, 2)->nullable()->after('status');
            }
            if (!Schema::hasColumn('projects', 'max_price')) {
                $table->decimal('max_price', 14, 2)->nullable()->after('min_price');
            }

            // BUA Columns
            if (!Schema::hasColumn('projects', 'min_bua')) {
                $table->decimal('min_bua', 10, 2)->nullable()->after('max_price');
            }
            if (!Schema::hasColumn('projects', 'max_bua')) {
                $table->decimal('max_bua', 10, 2)->nullable()->after('min_bua');
            }

            // Area & Units Columns
            if (!Schema::hasColumn('projects', 'total_area')) {
                $table->decimal('total_area', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('projects', 'built_up_ratio')) {
                $table->decimal('built_up_ratio', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('projects', 'total_units')) {
                $table->integer('total_units')->nullable();
            }
        });

        // Migrate data from legacy 'start_price' if it exists and 'min_price' is empty
        if (Schema::hasColumn('projects', 'start_price') && Schema::hasColumn('projects', 'min_price')) {
            // Use DB::statement to avoid model caching issues during migration
            DB::statement('UPDATE projects SET min_price = start_price WHERE min_price IS NULL AND start_price IS NOT NULL');
        }

        // Drop legacy 'start_price' column if it exists
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'start_price')) {
                $table->dropColumn('start_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Revert is tricky because we dropped start_price.
            // We can recreate it and copy back min_price.

            if (!Schema::hasColumn('projects', 'start_price')) {
                 $table->decimal('start_price', 15, 2)->nullable();
            }
        });

        if (Schema::hasColumn('projects', 'start_price') && Schema::hasColumn('projects', 'min_price')) {
             DB::statement('UPDATE projects SET start_price = min_price WHERE start_price IS NULL AND min_price IS NOT NULL');
        }

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'min_price',
                'max_price',
                'min_bua',
                'max_bua',
                'total_area',
                'built_up_ratio',
                'total_units'
            ]);
        });
    }
};
