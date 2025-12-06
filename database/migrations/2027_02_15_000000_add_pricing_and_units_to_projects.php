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
            if (!Schema::hasColumn('projects', 'min_price')) {
                $table->decimal('min_price', 15, 2)->nullable()->after('sales_launch_date');
            }
            if (!Schema::hasColumn('projects', 'max_price')) {
                $table->decimal('max_price', 15, 2)->nullable()->after('min_price');
            }
            if (!Schema::hasColumn('projects', 'min_bua')) {
                $table->decimal('min_bua', 10, 2)->nullable()->after('max_price');
            }
            if (!Schema::hasColumn('projects', 'max_bua')) {
                $table->decimal('max_bua', 10, 2)->nullable()->after('min_bua');
            }
            if (!Schema::hasColumn('projects', 'total_units')) {
                $table->integer('total_units')->nullable()->after('max_bua');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['min_price', 'max_price', 'min_bua', 'max_bua', 'total_units']);
        });
    }
};
