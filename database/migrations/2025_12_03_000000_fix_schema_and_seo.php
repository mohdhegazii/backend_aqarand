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
        // 1. Ensure is_active exists on all tables
        $tables = [
            'countries', 'regions', 'cities', 'districts',
            'property_types', 'unit_types', 'amenities',
            'segments', 'categories'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'is_active')) {
                    $table->boolean('is_active')->default(true);
                }
            });
        }

        // 2. Ensure image_path/image_url on lookups
        $lookupTables = ['property_types', 'unit_types', 'amenities', 'categories'];
        foreach ($lookupTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'image_path')) {
                    $table->string('image_path')->nullable();
                }
                if (!Schema::hasColumn($tableName, 'image_url')) {
                    $table->string('image_url')->nullable();
                }
            });
        }

        // 3. Update seo_meta for bilingual support
        Schema::table('seo_meta', function (Blueprint $table) {
            // Add EN fields
            if (!Schema::hasColumn('seo_meta', 'meta_title_en')) {
                $table->string('meta_title_en')->nullable()->after('meta_title');
            }
            if (!Schema::hasColumn('seo_meta', 'meta_description_en')) {
                $table->text('meta_description_en')->nullable()->after('meta_description');
            }
            if (!Schema::hasColumn('seo_meta', 'focus_keyphrase_en')) {
                $table->string('focus_keyphrase_en')->nullable()->after('focus_keyphrase');
            }

            // Add AR fields
            if (!Schema::hasColumn('seo_meta', 'meta_title_ar')) {
                $table->string('meta_title_ar')->nullable()->after('meta_title_en');
            }
            if (!Schema::hasColumn('seo_meta', 'meta_description_ar')) {
                $table->text('meta_description_ar')->nullable()->after('meta_description_en');
            }
            if (!Schema::hasColumn('seo_meta', 'focus_keyphrase_ar')) {
                $table->string('focus_keyphrase_ar')->nullable()->after('focus_keyphrase_en');
            }
        });

        // Migrate existing data if needed (optional, copy old to EN or leave empty)
        // DB::statement("UPDATE seo_meta SET meta_title_en = meta_title, meta_description_en = meta_description, focus_keyphrase_en = focus_keyphrase WHERE meta_title_en IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seo_meta', function (Blueprint $table) {
            $table->dropColumn([
                'meta_title_en', 'meta_description_en', 'focus_keyphrase_en',
                'meta_title_ar', 'meta_description_ar', 'focus_keyphrase_ar'
            ]);
        });

        // We won't drop is_active or image columns as they might be in use or created by previous migrations
    }
};
