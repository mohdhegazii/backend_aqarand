<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedBigInteger('district_id')->nullable()->change();
        });
    }

    public function down()
    {
        // Reverting to NOT NULL might fail if nulls exist
        // Schema::table('projects', function (Blueprint $table) {
        //     $table->unsignedBigInteger('district_id')->nullable(false)->change();
        // });
    }
};
