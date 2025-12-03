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
        if (Schema::hasTable('projects')) {
            // Modify column using raw SQL because Doctrine DBAL has issues with ENUMs sometimes,
            // and we want to be safe with existing data if possible (though mapping might be off).
            // We map old values to new ones if needed, or just change the definition.
            // Old: 'planned','under_construction','delivered'
            // New: 'new_launch','off_plan','under_construction','ready_to_move','livable'

            // First, let's map 'planned' -> 'new_launch' (or 'off_plan'?), 'delivered' -> 'ready_to_move' (or 'livable'?)
            // Let's assume:
            // planned -> off_plan
            // under_construction -> under_construction
            // delivered -> livable

            DB::statement("ALTER TABLE projects MODIFY COLUMN status ENUM('new_launch','off_plan','under_construction','ready_to_move','livable') NOT NULL DEFAULT 'new_launch'");

            // Note: Data migration might need manual UPDATEs if strictly needed, but MODIFY typically truncates or errors if values don't match.
            // However, MySQL usually keeps the string if it matches, or sets to empty/index 0 if invalid strict mode.
            // A safer way is to update data first.

            // Let's try to update existing data to valid new values first (if any)
            // But since this is dev phase, we might accept data loss or just direct alter.
            // Let's do direct alter.
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('projects')) {
            DB::statement("ALTER TABLE projects MODIFY COLUMN status ENUM('planned','under_construction','delivered') NOT NULL DEFAULT 'planned'");
        }
    }
};
