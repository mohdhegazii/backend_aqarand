<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_types', function (Blueprint $table) {
            if (!Schema::hasColumn('property_types', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('id')->constrained('categories')->nullOnDelete();
            }

            if (!Schema::hasColumn('property_types', 'group_key')) {
                $table->string('group_key')->nullable()->after('category_id');
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'segment_id')) {
                $table->dropForeign(['segment_id']);
                $table->dropColumn('segment_id');
            }
        });

        $rootCategories = [
            'Residential',
            'Coastal',
            'Commercial',
            'Lands',
        ];

        $categoryIds = [];
        foreach ($rootCategories as $name) {
            $slug = Str::slug($name);
            $existing = DB::table('categories')->where('slug', $slug)->first();

            if ($existing) {
                $categoryIds[$name] = $existing->id;
                continue;
            }

            $categoryIds[$name] = DB::table('categories')->insertGetId([
                'name_en' => $name,
                'name_ar' => $name,
                'slug' => $slug,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $typeMapping = [
            'office' => ['category' => 'Commercial', 'group_key' => 'administrative'],
            'company hq' => ['category' => 'Commercial', 'group_key' => 'administrative'],
            'co-working space' => ['category' => 'Commercial', 'group_key' => 'administrative'],
            'clinic' => ['category' => 'Commercial', 'group_key' => 'medical'],
            'medical center' => ['category' => 'Commercial', 'group_key' => 'medical'],
            'pharmacy' => ['category' => 'Commercial', 'group_key' => 'medical'],
            'hospital' => ['category' => 'Commercial', 'group_key' => 'medical'],
            'retail shop' => ['category' => 'Commercial', 'group_key' => 'retail'],
            'restaurant/cafe' => ['category' => 'Commercial', 'group_key' => 'retail'],
            'warehouse' => ['category' => 'Commercial', 'group_key' => 'industrial'],
            'factory' => ['category' => 'Commercial', 'group_key' => 'industrial'],
            'apartment' => ['category' => 'Residential', 'group_key' => 'living'],
            'villa' => ['category' => 'Residential', 'group_key' => 'living'],
            'twin house' => ['category' => 'Residential', 'group_key' => 'living'],
            'chalet' => ['category' => 'Coastal', 'group_key' => 'vacation'],
            'coastal villa' => ['category' => 'Coastal', 'group_key' => 'vacation'],
            'cabin' => ['category' => 'Coastal', 'group_key' => 'vacation'],
            'residential land' => ['category' => 'Lands', 'group_key' => 'building_lands'],
            'commercial land' => ['category' => 'Lands', 'group_key' => 'investment_lands'],
            'agriculture land' => ['category' => 'Lands', 'group_key' => 'agriculture'],
        ];

        $propertyTypes = DB::table('property_types')->get();
        foreach ($propertyTypes as $type) {
            $nameKey = strtolower(trim($type->name_en ?? $type->name_local ?? ''));
            $mapping = $typeMapping[$nameKey] ?? null;

            if (!$mapping && !empty($type->category)) {
                $mapping = match (strtolower($type->category)) {
                    'residential' => ['category' => 'Residential', 'group_key' => null],
                    'commercial', 'administrative', 'medical', 'mixed', 'other' => ['category' => 'Commercial', 'group_key' => strtolower($type->category) === 'commercial' ? null : strtolower($type->category)],
                    default => null,
                };
            }

            $categoryId = $mapping && isset($categoryIds[$mapping['category']]) ? $categoryIds[$mapping['category']] : null;
            $groupKey = $mapping['group_key'] ?? null;

            DB::table('property_types')
                ->where('id', $type->id)
                ->update([
                    'category_id' => $categoryId,
                    'group_key' => $groupKey,
                ]);
        }

        Schema::table('property_types', function (Blueprint $table) {
            if (Schema::hasColumn('property_types', 'category')) {
                $table->dropColumn('category');
            }
        });

        Schema::dropIfExists('segments');
    }

    public function down(): void
    {
        Schema::create('segments', function (Blueprint $table) {
            $table->id();
            $table->string('name_en');
            $table->string('name_ar');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'segment_id')) {
                $table->foreignId('segment_id')->nullable()->after('id')->constrained('segments')->nullOnDelete();
            }
        });

        Schema::table('property_types', function (Blueprint $table) {
            if (!Schema::hasColumn('property_types', 'category')) {
                $table->string('category')->nullable()->after('slug');
            }

            if (Schema::hasColumn('property_types', 'group_key')) {
                $table->dropColumn('group_key');
            }

            if (Schema::hasColumn('property_types', 'category_id')) {
                $table->dropConstrainedForeignId('category_id');
            }
        });
    }
};
