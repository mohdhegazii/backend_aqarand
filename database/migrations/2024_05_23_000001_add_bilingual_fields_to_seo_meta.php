<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('seo_meta')) {
            Schema::create('seo_meta', function (Blueprint $table) {
                $table->id();
                $table->morphs('seoable');
                $table->string('meta_title_en')->nullable();
                $table->string('meta_title_ar')->nullable();
                $table->text('meta_description_en')->nullable();
                $table->text('meta_description_ar')->nullable();
                $table->string('focus_keyphrase_en')->nullable();
                $table->string('focus_keyphrase_ar')->nullable();
                $table->json('meta_data')->nullable();
                $table->timestamps();
            });
            return;
        }

        Schema::table('seo_meta', function (Blueprint $table) {
            if (!Schema::hasColumn('seo_meta', 'meta_title_en')) {
                $table->string('meta_title_en')->nullable();
            }
            if (!Schema::hasColumn('seo_meta', 'meta_title_ar')) {
                $table->string('meta_title_ar')->nullable();
            }
            if (!Schema::hasColumn('seo_meta', 'meta_description_en')) {
                $table->text('meta_description_en')->nullable();
            }
            if (!Schema::hasColumn('seo_meta', 'meta_description_ar')) {
                $table->text('meta_description_ar')->nullable();
            }
            if (!Schema::hasColumn('seo_meta', 'focus_keyphrase_en')) {
                $table->string('focus_keyphrase_en')->nullable();
            }
            if (!Schema::hasColumn('seo_meta', 'focus_keyphrase_ar')) {
                $table->string('focus_keyphrase_ar')->nullable();
            }
            if (!Schema::hasColumn('seo_meta', 'meta_data')) {
                $table->json('meta_data')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('seo_meta')) {
             Schema::table('seo_meta', function (Blueprint $table) {
                if (Schema::hasColumn('seo_meta', 'meta_title_en')) $table->dropColumn('meta_title_en');
                if (Schema::hasColumn('seo_meta', 'meta_title_ar')) $table->dropColumn('meta_title_ar');
                if (Schema::hasColumn('seo_meta', 'meta_description_en')) $table->dropColumn('meta_description_en');
                if (Schema::hasColumn('seo_meta', 'meta_description_ar')) $table->dropColumn('meta_description_ar');
                if (Schema::hasColumn('seo_meta', 'focus_keyphrase_en')) $table->dropColumn('focus_keyphrase_en');
                if (Schema::hasColumn('seo_meta', 'focus_keyphrase_ar')) $table->dropColumn('focus_keyphrase_ar');
                if (Schema::hasColumn('seo_meta', 'meta_data')) $table->dropColumn('meta_data');
             });
        }
    }
};
