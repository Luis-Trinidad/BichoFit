<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(ExerciseSeeder::class);
        // Dataset completo (1,324 ejercicios + GIFs): auto-descarga el media
        // la primera vez en producción; en dev usa el dataset de /Users/... si existe
        $this->call(ExerciseDatasetSeeder::class);

        // Usuario de prueba solo en desarrollo (siembra idempotente en producción)
        if (! app()->environment('production')) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }
    }
}
