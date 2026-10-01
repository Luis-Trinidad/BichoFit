<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\Routine;
use App\Models\RoutineItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RoutineItem> */
class RoutineItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'routine_id' => Routine::factory(),
            'exercise_id' => Exercise::factory()->catalog(),
            'position' => 0,
            'target' => null,
        ];
    }
}
