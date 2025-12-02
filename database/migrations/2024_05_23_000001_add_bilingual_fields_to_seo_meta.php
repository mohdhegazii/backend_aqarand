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
        Schema::table('seo_meta', function (Blueprint $table) {
            // English fields
            $table->string('meta_title_en')->nullable();
            $table->text('meta_description_en')->nullable();
            $table->string('focus_keyphrase_en')->nullable();

            // Arabic fields
            $table->string('meta_title_ar')->nullable();
            $table->text('meta_description_ar')->nullable();
            $table->string('focus_keyphrase_ar')->nullable();
        });

        // Migrate existing data (Optional: assuming current data is mixed or default)
        // Since we are adding new columns, we can just leave the old ones for now or map them.
        // Let's assume the old ones were English or generic. We won't copy them automatically to avoid confusion,
        // unless requested. But for safety, we keep the old columns in the DB, just not adding them to the new schema
        // if they were already there (which they are).
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seo_meta', function (Blueprint $table) {
            $table->dropColumn([
                'meta_title_en',
                'meta_description_en',
                'focus_keyphrase_en',
                'meta_title_ar',
                'meta_description_ar',
                'focus_keyphrase_ar',
            ]);
        });
    }
};
