<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Exercise> */
class ExerciseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'muscle_group' => fake()->randomElement([
                'Pecho', 'Espalda', 'Hombros', 'Bíceps', 'Tríceps',
                'Piernas', 'Glúteos', 'Core', 'Cardio',
            ]),
            'user_id' => null,
        ];
    }

    /** Ejercicio del catálogo global (user_id null). */
    public function catalog(): static
    {
        return $this->state(fn () => ['user_id' => null]);
    }

    /** Ejercicio personal de un usuario. */
    public function custom(User $user): static
    {
        return $this->state(fn () => ['user_id' => $user->id]);
    }
}
