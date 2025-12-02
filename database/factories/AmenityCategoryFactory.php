<?php

namespace Database\Factories;

use App\Models\AmenityCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\AmenityCategory>
 */
class AmenityCategoryFactory extends Factory
{
    protected $model = AmenityCategory::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'name_en' => ucfirst($name),
            'name_ar' => ucfirst($name),
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(1, 9999),
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(0, 100),
        ];
    }
}
