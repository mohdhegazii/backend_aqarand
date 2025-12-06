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
            // Bilingual Names
            if (!Schema::hasColumn('projects', 'name_ar')) {
                $table->string('name_ar')->nullable()->after('name');
            }
            if (!Schema::hasColumn('projects', 'name_en')) {
                $table->string('name_en')->nullable()->after('name_ar');
            }

            // Project Area
            if (!Schema::hasColumn('projects', 'project_area_value')) {
                $table->decimal('project_area_value', 12, 2)->nullable()->after('slug');
            }
            if (!Schema::hasColumn('projects', 'project_area_unit')) {
                $table->enum('project_area_unit', ['feddan', 'sqm'])->default('sqm')->after('project_area_value');
            }

            // Master Project
            if (!Schema::hasColumn('projects', 'is_part_of_master_project')) {
                $table->boolean('is_part_of_master_project')->default(false)->after('project_area_unit');
            }
            if (!Schema::hasColumn('projects', 'master_project_id')) {
                $table->foreignId('master_project_id')->nullable()->after('is_part_of_master_project')->constrained('projects')->nullOnDelete();
            }

            // Sales Launch Date
            if (!Schema::hasColumn('projects', 'sales_launch_date')) {
                $table->date('sales_launch_date')->nullable()->after('delivery_year');
            }

            // Flags
            if (!Schema::hasColumn('projects', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('status');
            }
            if (!Schema::hasColumn('projects', 'is_top_project')) {
                $table->boolean('is_top_project')->default(false)->after('is_featured');
            }
            if (!Schema::hasColumn('projects', 'include_in_sitemap')) {
                $table->boolean('include_in_sitemap')->default(true)->after('is_top_project');
            }

            // Publish Status
            if (!Schema::hasColumn('projects', 'publish_status')) {
                $table->enum('publish_status', ['draft', 'published'])->default('draft')->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We generally don't drop columns in a 'fix' or 'ensure' migration
        // because we don't know if they were added here or elsewhere.
        // But for completeness, we can list them wrapped in checks.
        Schema::table('projects', function (Blueprint $table) {
            // Safety check before dropping foreign key
            if (Schema::hasColumn('projects', 'master_project_id')) {
                try {
                    $table->dropForeign(['master_project_id']);
                } catch (\Exception $e) {
                    // Ignore if FK doesn't exist
                }
            }

            $columns = [
                'name_ar', 'name_en',
                'project_area_value', 'project_area_unit',
                'is_part_of_master_project', 'master_project_id',
                'sales_launch_date',
                'is_featured', 'is_top_project', 'include_in_sitemap',
                'publish_status'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('projects', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
