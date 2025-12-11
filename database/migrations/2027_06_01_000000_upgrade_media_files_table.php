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
        Schema::table('media_files', function (Blueprint $table) {
            // New columns
            if (!Schema::hasColumn('media_files', 'type')) {
                $table->string('type', 20)->nullable()->comment('image, pdf, video, other')->index();
            }
            if (!Schema::hasColumn('media_files', 'original_name')) {
                $table->string('original_name', 255)->nullable()->after('path');
            }
            if (!Schema::hasColumn('media_files', 'alt_text')) {
                $table->string('alt_text', 255)->nullable();
            }
            if (!Schema::hasColumn('media_files', 'title')) {
                $table->string('title', 255)->nullable();
            }
            if (!Schema::hasColumn('media_files', 'caption')) {
                $table->text('caption')->nullable();
            }
            if (!Schema::hasColumn('media_files', 'seo_slug')) {
                $table->string('seo_slug', 255)->nullable()->index();
            }
            if (!Schema::hasColumn('media_files', 'uploaded_by_id')) {
                $table->unsignedBigInteger('uploaded_by_id')->nullable()->index();
                // We don't add constraint yet to avoid issues if users table is non-standard or seeded differently,
                // but usually it references users.id
            }
            if (!Schema::hasColumn('media_files', 'is_system_asset')) {
                $table->boolean('is_system_asset')->default(false);
            }
            if (!Schema::hasColumn('media_files', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media_files', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'original_name',
                'alt_text',
                'title',
                'caption',
                'seo_slug',
                'uploaded_by_id',
                'is_system_asset',
                'deleted_at'
            ]);
        });
    }
};
