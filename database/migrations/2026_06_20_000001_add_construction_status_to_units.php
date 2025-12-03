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
        if (Schema::hasTable('units')) {
            Schema::table('units', function (Blueprint $table) {
                $table->enum('construction_status', ['new_launch','off_plan','under_construction','ready_to_move','livable'])
                      ->default('new_launch')
                      ->after('unit_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('units')) {
            Schema::table('units', function (Blueprint $table) {
                $table->dropColumn('construction_status');
            });
        }
    }
};
