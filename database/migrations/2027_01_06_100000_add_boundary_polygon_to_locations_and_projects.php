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
        $tables = ['countries', 'regions', 'cities', 'districts', 'projects'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'boundary_polygon')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->json('boundary_polygon')->nullable()->after('updated_at');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['countries', 'regions', 'cities', 'districts', 'projects'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'boundary_polygon')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('boundary_polygon');
                });
            }
        }
    }
};
