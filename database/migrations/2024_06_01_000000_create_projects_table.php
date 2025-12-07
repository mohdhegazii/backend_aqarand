<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('projects')) {
            Schema::create('projects', function (Blueprint $table) {
                $table->id();

                $table->string('name');
                $table->string('slug')->unique();
                $table->string('tagline')->nullable();
                $table->text('description_long')->nullable();

                $table->string('status')->default('under_construction');

                // --- التعديل الجديد: أعمدة السيو (SEO) ---
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                // ---------------------------------------

                $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
                $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();

                $table->decimal('lat', 10, 7)->nullable();
                $table->decimal('lng', 10, 7)->nullable();

                $table->string('address_text')->nullable();

                $table->string('main_image')->nullable();
                $table->decimal('start_price', 15, 2)->nullable();
                $table->decimal('max_price', 15, 2)->nullable();

                $table->boolean('is_featured')->default(false);
                $table->boolean('is_active')->default(true);
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};