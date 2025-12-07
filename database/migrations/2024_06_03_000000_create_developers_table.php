<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('developers')) {
            Schema::create('developers', function (Blueprint $table) {
                $table->id();

                // البيانات الأساسية
                $table->string('name'); // الاسم الأساسي (اللي التحديث هيحاول يضيف عليه الإنجليزي)
                $table->string('slug')->unique();
                $table->text('description')->nullable();

                // اللوجو والبيانات
                $table->string('logo')->nullable();
                $table->string('website')->nullable();

                // حالة النشاط
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->nullable();

                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('developers');
    }
};