<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            if (!Schema::hasColumn('countries', 'lat')) {
                // Assuming name_local exists from schema.sql, or we append to end
                // We'll try to place it after name_local if it exists, otherwise just add it
                $table->decimal('lat', 10, 7)->nullable()->after('name_local');
            }
            if (!Schema::hasColumn('countries', 'lng')) {
                $table->decimal('lng', 10, 7)->nullable()->after('lat');
            }
        });
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            if (Schema::hasColumn('countries', 'lat')) {
                $table->dropColumn('lat');
            }
            if (Schema::hasColumn('countries', 'lng')) {
                $table->dropColumn('lng');
            }
        });
    }
};
