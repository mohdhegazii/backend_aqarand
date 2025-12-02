<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Region>
 */
class RegionFactory extends Factory
{
    protected $model = Region::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->city();

        return [
            'country_id' => Country::factory(),
            'name_en' => $name,
            'name_local' => $name,
            'slug' => Str::slug($name),
            'is_active' => true,
            'lat' => $this->faker->randomFloat(7, -90, 90),
            'lng' => $this->faker->randomFloat(7, -180, 180),
        ];
    }
}
