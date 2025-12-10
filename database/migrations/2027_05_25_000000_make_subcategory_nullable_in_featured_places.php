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
        // Superseded by 2027_05_28_000000_fix_featured_places_nullable_final.php
        // This migration originally required doctrine/dbal which might be missing.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
