<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Update Projects table status to support new values
        // We convert to VARCHAR to avoid ENUM constraints issues during dev
        Schema::table('projects', function (Blueprint $table) {
            $table->string('status', 50)->default('new_launch')->change();
        });

        // 2. Add construction_status to Units
        Schema::table('units', function (Blueprint $table) {
            $table->string('construction_status', 50)->default('new_launch')->after('unit_status');
        });
    }

    public function down()
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('construction_status');
        });

        // Reverting project status is hard if data exists, but we can try
        // DB::statement("ALTER TABLE projects MODIFY COLUMN status ENUM('planned','under_construction','delivered') DEFAULT 'planned'");
    }
};
