<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'construction_status')) {
                $table->enum('construction_status', ['planned', 'under_construction', 'delivered'])
                    ->default('planned')
                    ->after('status');
            }
        });

        if (Schema::hasColumn('projects', 'construction_status') && Schema::hasColumn('projects', 'status')) {
            DB::table('projects')
                ->whereIn('status', ['planned', 'under_construction', 'delivered'])
                ->update(['construction_status' => DB::raw('status')]);
        }

        if (Schema::hasColumn('projects', 'status')) {
            if (Schema::hasColumn('projects', 'publish_status')) {
                DB::table('projects')
                    ->whereIn('publish_status', ['draft', 'published'])
                    ->update(['status' => DB::raw('publish_status')]);
            }

            DB::table('projects')
                ->whereNotIn('status', ['draft', 'published'])
                ->update(['status' => 'draft']);

            DB::statement("ALTER TABLE projects MODIFY status ENUM('draft','published') NOT NULL DEFAULT 'draft'");
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('projects', 'status')) {
            DB::statement("ALTER TABLE projects MODIFY status ENUM('planned','under_construction','delivered') NOT NULL DEFAULT 'planned'");
        }

        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'construction_status')) {
                $table->dropColumn('construction_status');
            }
        });
    }
};
