<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Check and add developer_id if missing
            if (!Schema::hasColumn('projects', 'developer_id')) {
                // If the table exists but column is missing
                // We add it after 'id' or 'country_id' depending on typical structure
                $table->foreignId('developer_id')->nullable()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Usually we wouldn't drop this in a fix migration to avoid data loss
            // $table->dropColumn('developer_id');
        });
    }
};
