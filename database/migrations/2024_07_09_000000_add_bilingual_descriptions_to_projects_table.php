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
            if (!Schema::hasColumn('projects', 'title_ar')) {
                $table->string('title_ar')->nullable()->after('description_long');
            }

            if (!Schema::hasColumn('projects', 'title_en')) {
                $table->string('title_en')->nullable()->after('title_ar');
            }

            if (!Schema::hasColumn('projects', 'description_ar')) {
                $table->text('description_ar')->nullable()->after('title_en');
            }

            if (!Schema::hasColumn('projects', 'description_en')) {
                $table->text('description_en')->nullable()->after('description_ar');
            }
        });

        if (Schema::hasColumn('projects', 'description_long') && Schema::hasColumn('projects', 'description_en')) {
            DB::table('projects')
                ->whereNull('description_en')
                ->whereNotNull('description_long')
                ->update(['description_en' => DB::raw('description_long')]);
        }
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'description_en')) {
                $table->dropColumn('description_en');
            }

            if (Schema::hasColumn('projects', 'description_ar')) {
                $table->dropColumn('description_ar');
            }

            if (Schema::hasColumn('projects', 'title_en')) {
                $table->dropColumn('title_en');
            }

            if (Schema::hasColumn('projects', 'title_ar')) {
                $table->dropColumn('title_ar');
            }
        });
    }
};
