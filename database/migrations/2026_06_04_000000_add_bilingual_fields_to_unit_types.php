<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unit_types', function (Blueprint $table) {
            if (!Schema::hasColumn('unit_types', 'name_en')) {
                $table->string('name_en', 150)->nullable()->after('property_type_id');
            }

            if (!Schema::hasColumn('unit_types', 'name_local')) {
                $table->string('name_local', 150)->nullable()->after('name_en');
            }
        });

        DB::table('unit_types')->select('id', 'name', 'name_en', 'name_local')->orderBy('id')->chunk(100, function ($types) {
            foreach ($types as $type) {
                DB::table('unit_types')
                    ->where('id', $type->id)
                    ->update([
                        'name_en' => $type->name_en ?? $type->name,
                        'name_local' => $type->name_local ?? $type->name,
                    ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('unit_types', function (Blueprint $table) {
            if (Schema::hasColumn('unit_types', 'name_en')) {
                $table->dropColumn('name_en');
            }

            if (Schema::hasColumn('unit_types', 'name_local')) {
                $table->dropColumn('name_local');
            }
        });
    }
};
