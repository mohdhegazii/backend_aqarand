<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('projects', 'map_polygon')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->json('map_polygon')->nullable()->after('lng');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('projects', 'map_polygon')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('map_polygon');
            });
        }
    }
};
