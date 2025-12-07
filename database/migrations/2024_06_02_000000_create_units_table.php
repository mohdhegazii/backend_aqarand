<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('units')) {
            Schema::create('units', function (Blueprint $table) {
                $table->id();

                $table->foreignId('project_id')->nullable()->constrained('projects')->cascadeOnDelete();
                $table->foreignId('property_type_id')->nullable()->constrained('property_types')->nullOnDelete();
                $table->foreignId('unit_type_id')->nullable()->constrained('unit_types')->nullOnDelete();

                $table->string('unit_number')->nullable();
                $table->string('title')->nullable();
                $table->string('slug')->unique()->nullable();

                $table->text('description')->nullable();

                $table->decimal('price', 15, 2)->default(0);
                $table->double('area')->nullable();
                $table->double('land_area')->nullable();

                $table->integer('bedrooms')->nullable();
                $table->integer('bathrooms')->nullable();
                $table->integer('floor_number')->nullable();

                $table->string('unit_status')->default('available');

                $table->string('main_image')->nullable();

                // --- التعديلات الجديدة ---
                $table->text('media')->nullable(); // عشان التحديث يلاقي مكانه
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                // -----------------------

                $table->boolean('is_active')->default(true);
                $table->boolean('is_featured')->default(false);

                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};