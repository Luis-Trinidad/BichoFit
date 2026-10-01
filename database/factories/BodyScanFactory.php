<?php

namespace Database\Factories;

use App\Models\BodyScan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BodyScan> */
class BodyScanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'scanned_at' => fake()->dateTimeThisYear(),
            'weight_kg' => fake()->randomFloat(1, 55, 110),
            'body_fat_pct' => fake()->randomFloat(1, 8, 35),
            'muscle_mass_kg' => fake()->randomFloat(1, 25, 60),
            'water_pct' => fake()->randomFloat(1, 40, 65),
            'bmi' => fake()->randomFloat(1, 17, 35),
            'visceral_fat' => fake()->numberBetween(2, 15),
            'metabolic_age' => fake()->numberBetween(15, 60),
            'source' => 'manual',
        ];
    }
}
