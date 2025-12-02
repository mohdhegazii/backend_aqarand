<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Country>
 */
class CountryFactory extends Factory
{
    protected $model = Country::class;

    public function definition(): array
    {
        $name = $this->faker->country();

        return [
            'code' => strtoupper($this->faker->unique()->lexify('??')),
            'name_en' => $name,
            'name_local' => $name,
            'lat' => $this->faker->randomFloat(7, -90, 90),
            'lng' => $this->faker->randomFloat(7, -180, 180),
            'is_active' => true,
        ];
    }
}
