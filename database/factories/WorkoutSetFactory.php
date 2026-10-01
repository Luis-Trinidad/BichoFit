<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\WorkoutSession;
use App\Models\WorkoutSet;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<WorkoutSet> */
class WorkoutSetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'session_id' => WorkoutSession::factory(),
            'exercise_id' => Exercise::factory()->catalog(),
            'reps' => fake()->numberBetween(5, 12),
            'weight_kg' => fake()->randomFloat(2, 20, 120),
        ];
    }
}
