<?php

namespace Database\Factories;

use App\Models\Routine;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Routine> */
class RoutineFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->randomElement(['Push', 'Pull', 'Pierna', 'Full body', 'Torso']),
            'notes' => null,
        ];
    }
}
