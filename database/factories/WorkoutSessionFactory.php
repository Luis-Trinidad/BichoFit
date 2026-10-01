<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WorkoutSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<WorkoutSession> */
class WorkoutSessionFactory extends Factory
{
    public function definition(): array
    {
        $startedAt = fake()->dateTimeThisYear();

        return [
            'user_id' => User::factory(),
            'date' => $startedAt->format('Y-m-d'),
            'notes' => null,
            'started_at' => $startedAt,
            'finished_at' => $startedAt->modify('+75 minutes'),
        ];
    }

    /** Sesión en curso (sin terminar). */
    public function active(): static
    {
        return $this->state(fn () => ['finished_at' => null]);
    }
}
