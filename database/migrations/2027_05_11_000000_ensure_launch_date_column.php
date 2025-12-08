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
        // Ensure projects table exists
        if (Schema::hasTable('projects')) {
            Schema::table('projects', function (Blueprint $table) {
                // Add launch_date if it does not exist
                if (!Schema::hasColumn('projects', 'launch_date')) {
                    $table->date('launch_date')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('projects')) {
            Schema::table('projects', function (Blueprint $table) {
                if (Schema::hasColumn('projects', 'launch_date')) {
                    $table->dropColumn('launch_date');
                }
            });
        }
    }
};
