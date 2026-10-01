<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\User;
use Database\Seeders\ExerciseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExerciseModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_puebla_el_catalogo_global(): void
    {
        $this->seed(ExerciseSeeder::class);

        $this->assertSame(
            Exercise::whereNull('user_id')->count(),
            Exercise::count(),
            'Todo ejercicio sembrado debe ser del catálogo global (user_id null)'
        );
        $this->assertGreaterThan(50, Exercise::count(), 'El catálogo debe traer 50+ ejercicios');
        $this->assertTrue(
            Exercise::where('muscle_group', 'Pecho')->exists()
            && Exercise::where('muscle_group', 'Piernas')->exists()
        );
    }

    public function test_scope_for_user_incluye_catalogo_y_propios_excluye_ajenos(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();

        $catalog = Exercise::factory()->catalog()->createMany([['name' => 'Press de banca con barra', 'muscle_group' => 'Pecho']]);
        $own = Exercise::factory()->custom($alice)->create();
        $foreign = Exercise::factory()->custom($bob)->create();

        $visible = Exercise::forUser($alice->id)->pluck('id');

        $this->assertContains($catalog[0]->id, $visible);
        $this->assertContains($own->id, $visible);
        $this->assertNotContains($foreign->id, $visible);
    }

    public function test_police_permite_editar_solo_ejercicios_propios(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $catalogExercise = Exercise::factory()->catalog()->create();
        $ownCustom = Exercise::factory()->custom($alice)->create();
        $foreignCustom = Exercise::factory()->custom($bob)->create();

        $this->assertTrue($alice->can('update', $ownCustom));
        $this->assertTrue($alice->can('delete', $ownCustom));

        $this->assertFalse($alice->can('update', $catalogExercise), 'El catálogo global es inmutable');
        $this->assertFalse($alice->can('delete', $catalogExercise));
        $this->assertFalse($alice->can('update', $foreignCustom));
        $this->assertFalse($alice->can('delete', $foreignCustom));
    }
}
