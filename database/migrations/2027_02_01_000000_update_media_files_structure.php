<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('media_files', function (Blueprint $table) {
            if (!Schema::hasColumn('media_files', 'collection_name')) {
                $table->string('collection_name', 50)->default('default')->index()->after('context_id');
            }
            if (!Schema::hasColumn('media_files', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('is_primary');
            }
            if (!Schema::hasColumn('media_files', 'is_private')) {
                $table->boolean('is_private')->default(false)->after('disk');
            }
        });
    }

    public function down()
    {
        Schema::table('media_files', function (Blueprint $table) {
            $table->dropColumn(['collection_name', 'sort_order', 'is_private']);
        });
    }
};
