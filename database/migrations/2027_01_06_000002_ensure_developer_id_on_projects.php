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
            // Check if the column exists first to be idempotent
            if (!Schema::hasColumn('projects', 'developer_id')) {
                // Add the column
                // We assume 'id' is the primary key. If 'country_id' is first, we can place it after that or just after id.
                // The error shows 'country_id' is the first foreign key inserted.
                $table->foreignId('developer_id')->nullable()->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'developer_id')) {
                // We can't easily drop foreign keys without knowing the exact name Laravel generated
                // So we try-catch the drop foreign if needed, or just drop the column which drops the constraint often.
                // However, to be safe in a 'down' migration, we might skip or use try/catch.
                // For now, standard dropColumn is usually fine if we accept the constraint might be dropped too.
                try {
                     $table->dropForeign(['developer_id']);
                } catch (\Exception $e) {
                    // Ignore if FK doesn't exist
                }
                $table->dropColumn('developer_id');
            }
        });
    }
};
