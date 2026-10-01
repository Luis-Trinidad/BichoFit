<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\User;
use App\Models\WorkoutSession;
use App\Models\WorkoutSet;
use Database\Seeders\ExerciseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as InertiaPage;
use Tests\TestCase;

class WorkoutFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_invitado_es_redirigido_a_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->post(route('workout-sessions.store'))->assertRedirect(route('login'));
    }

    public function test_usuario_inicia_sesion_de_entrenamiento_y_ve_la_pantalla(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('workout-sessions.store'));

        $session = WorkoutSession::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($session);
        $this->assertNull($session->finished_at);

        $response->assertRedirect(route('workout-sessions.show', $session));

        $this->get(route('workout-sessions.show', $session))
            ->assertOk()
            ->assertInertia(fn (InertiaPage $page) => $page
                ->component('workout/Show')
                ->where('session.id', $session->id)
                ->has('exercises'));
    }

    public function test_usuario_registra_series_en_su_sesion_activa(): void
    {
        $user = User::factory()->create();
        $this->seed(ExerciseSeeder::class);
        $session = WorkoutSession::factory()->for($user)->active()->create();
        $exercise = Exercise::whereNull('user_id')->first();

        $this->actingAs($user)
            ->post(route('workout-sets.store', ['session' => $session->id]), [
                'exercise_id' => $exercise->id,
                'reps' => 10,
                'weight_kg' => 80.5,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('workout_sets', [
            'session_id' => $session->id,
            'exercise_id' => $exercise->id,
            'reps' => 10,
            'weight_kg' => 80.5,
        ]);
    }

    public function test_no_se_pueden_agregar_series_a_una_sesion_terminada(): void
    {
        $user = User::factory()->create();
        $this->seed(ExerciseSeeder::class);
        $session = WorkoutSession::factory()->for($user)->create(); // terminada
        $exercise = Exercise::whereNull('user_id')->first();

        $this->actingAs($user)
            ->post(route('workout-sets.store', ['session' => $session->id]), [
                'exercise_id' => $exercise->id,
                'reps' => 10,
                'weight_kg' => 50,
            ])
            ->assertSessionHasErrors('session');

        $this->assertDatabaseMissing('workout_sets', ['session_id' => $session->id]);
    }

    public function test_sesion_ajena_no_existe_para_otro_usuario(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $session = WorkoutSession::factory()->for($owner)->active()->create();
        $this->seed(ExerciseSeeder::class);
        $exercise = Exercise::whereNull('user_id')->first();

        // Ver detalle: policy → 403
        $this->actingAs($intruder)
            ->get(route('workout-sessions.show', $session))
            ->assertForbidden();

        // Agregar serie: 404 (no filtrar existencia)
        $this->actingAs($intruder)
            ->post(route('workout-sets.store', ['session' => $session->id]), [
                'exercise_id' => $exercise->id,
                'reps' => 10,
                'weight_kg' => 50,
            ])
            ->assertNotFound();

        // Editar/borrar serie ajena: policy → 403
        $set = WorkoutSet::factory()->for($session, 'session')->create();
        $this->actingAs($intruder)
            ->patch(route('workout-sets.update', $set), ['reps' => 1])
            ->assertForbidden();
    }

    public function test_usuario_edita_y_borra_sus_series(): void
    {
        $user = User::factory()->create();
        $session = WorkoutSession::factory()->for($user)->active()->create();
        $set = WorkoutSet::factory()->for($session, 'session')->create(['reps' => 10, 'weight_kg' => 60]);

        $this->actingAs($user)
            ->patch(route('workout-sets.update', $set), ['reps' => 8])
            ->assertRedirect();
        $this->assertDatabaseHas('workout_sets', ['id' => $set->id, 'reps' => 8, 'weight_kg' => 60]);

        $this->actingAs($user)
            ->delete(route('workout-sets.destroy', $set))
            ->assertRedirect();
        $this->assertDatabaseMissing('workout_sets', ['id' => $set->id]);
    }

    public function test_terminar_sesion_marca_finished_at_y_vuelve_al_dashboard(): void
    {
        $user = User::factory()->create();
        $session = WorkoutSession::factory()->for($user)->active()->create();

        $this->actingAs($user)
            ->post(route('workout-sessions.finish', $session))
            ->assertRedirect(route('dashboard'));

        $this->assertNotNull($session->fresh()->finished_at);
    }

    public function test_historial_muestra_solo_sesiones_terminadas_propias(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $finished = WorkoutSession::factory()->for($user)->create();
        $active = WorkoutSession::factory()->for($user)->active()->create();
        WorkoutSession::factory()->for($other)->count(2)->create();

        $response = $this->actingAs($user)->get(route('history.index'));

        $response->assertOk()->assertInertia(fn (InertiaPage $page) => $page
            ->component('history/Index')
            ->has('sessions.data', 1)
            ->where('sessions.data.0.id', $finished->id));
    }

    public function test_dashboard_muestra_sesion_activa_racha_y_semana(): void
    {
        $user = User::factory()->create();
        $active = WorkoutSession::factory()->for($user)->active()->create();
        WorkoutSet::factory()->for($active, 'session')->create(['reps' => 10, 'weight_kg' => 100]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        // La sesión activa no cuenta en las sesiones terminadas de la semana
        $response->assertOk()->assertInertia(fn (InertiaPage $page) => $page
            ->component('Dashboard')
            ->where('activeSession.id', $active->id)
            ->where('week.sessions', 0)
            ->where('week.streak', 0));

        // Racha semanal: hoy y ayer (misma semana, 2 sesiones) aún no completan la semana
        WorkoutSession::factory()->for($user)->create(['date' => today()]);
        WorkoutSession::factory()->for($user)->create(['date' => today()->subDay()]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertInertia(fn (InertiaPage $page) => $page
                ->where('week.sessions', 2)
                ->where('week.streak', 0));

        // Tres sesiones en la semana → semana completa → racha 1
        WorkoutSession::factory()->for($user)->create(['date' => today()->subDays(2)]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertInertia(fn (InertiaPage $page) => $page
                ->where('week.sessions', 3)
                ->where('week.streak', 1));
    }

    public function test_usuario_crea_ejercicio_personal_y_no_duplica(): void
    {
        $user = User::factory()->create();
        $this->seed(ExerciseSeeder::class);

        $this->actingAs($user)
            ->post(route('exercises.store'), [
                'name' => 'Press landmine',
                'muscle_group' => 'Hombros',
            ])
            ->assertRedirect(route('exercises.index'));

        $this->assertDatabaseHas('exercises', [
            'name' => 'Press landmine',
            'user_id' => $user->id,
            'muscle_group' => 'Hombros',
        ]);

        // Duplicado contra el catálogo global
        $this->actingAs($user)
            ->post(route('exercises.store'), [
                'name' => 'Dominadas',
                'muscle_group' => 'Espalda',
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_usuario_no_puede_borrar_ejercicios_del_catalogo(): void
    {
        $user = User::factory()->create();
        $this->seed(ExerciseSeeder::class);
        $catalogExercise = Exercise::whereNull('user_id')->first();

        $this->actingAs($user)
            ->delete(route('exercises.destroy', $catalogExercise))
            ->assertForbidden();

        $this->assertDatabaseHas('exercises', ['id' => $catalogExercise->id]);
    }

    public function test_serie_con_ejercicio_ajeno_se_rechaza(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $session = WorkoutSession::factory()->for($user)->active()->create();
        $foreignExercise = Exercise::factory()->custom($other)->create();

        $this->actingAs($user)
            ->post(route('workout-sets.store', ['session' => $session->id]), [
                'exercise_id' => $foreignExercise->id,
                'reps' => 10,
                'weight_kg' => 50,
            ])
            ->assertSessionHasErrors('exercise_id');
    }
}
