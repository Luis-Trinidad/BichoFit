<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\User;
use App\Models\WorkoutSession;
use App\Models\WorkoutSet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkoutSessionModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_active_filtra_solo_sesiones_sin_terminar(): void
    {
        $user = User::factory()->create();
        $active = WorkoutSession::factory()->for($user)->active()->create();
        $finished = WorkoutSession::factory()->for($user)->create();

        $activeIds = WorkoutSession::active()->pluck('id');

        $this->assertContains($active->id, $activeIds);
        $this->assertNotContains($finished->id, $activeIds);
    }

    public function test_volume_kg_suma_reps_por_peso_de_todas_las_series(): void
    {
        $session = WorkoutSession::factory()->create();
        $exercise = Exercise::factory()->catalog()->create();
        WorkoutSet::factory()->for($session, 'session')->for($exercise)->create(['reps' => 10, 'weight_kg' => 100]);
        WorkoutSet::factory()->for($session, 'session')->for($exercise)->create(['reps' => 8, 'weight_kg' => 90]);

        $this->assertEquals(1720.0, $session->volumeKg());
    }

    public function test_policy_permite_ver_modificar_borrar_solo_al_dueno(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $session = WorkoutSession::factory()->for($owner)->create();

        $this->assertTrue($owner->can('view', $session));
        $this->assertTrue($owner->can('update', $session));
        $this->assertTrue($owner->can('delete', $session));

        $this->assertFalse($intruder->can('view', $session));
        $this->assertFalse($intruder->can('update', $session));
        $this->assertFalse($intruder->can('delete', $session));
    }

    public function test_borrar_sesion_elimina_sus_series(): void
    {
        $session = WorkoutSession::factory()->create();
        $set = WorkoutSet::factory()->for($session, 'session')->create();

        $session->delete();

        $this->assertDatabaseMissing('workout_sets', ['id' => $set->id]);
    }
}
