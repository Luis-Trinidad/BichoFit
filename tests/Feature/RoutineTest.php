<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\Routine;
use App\Models\RoutineItem;
use App\Models\User;
use App\Models\WorkoutSession;
use Database\Seeders\ExerciseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as InertiaPage;
use Tests\TestCase;

class RoutineTest extends TestCase
{
    use RefreshDatabase;

    private function exerciseFromCatalog(): Exercise
    {
        $this->seed(ExerciseSeeder::class);

        return Exercise::whereNull('user_id')->first();
    }

    public function test_usuario_crea_rutina_y_agrega_ejercicios_en_orden(): void
    {
        $user = User::factory()->create();
        $exerciseA = $this->exerciseFromCatalog();
        $exerciseB = Exercise::whereNull('user_id')->skip(1)->first();

        $response = $this->actingAs($user)
            ->post(route('routines.store'), ['name' => 'Push A']);
        $routine = Routine::where('user_id', $user->id)->first();
        $response->assertRedirect(route('routines.show', $routine));

        $this->actingAs($user)->post(route('routine-items.store', $routine), [
            'exercise_id' => $exerciseA->id, 'target' => '3x8-12',
        ])->assertRedirect();
        $this->actingAs($user)->post(route('routine-items.store', $routine), [
            'exercise_id' => $exerciseB->id,
        ])->assertRedirect();

        $this->assertSame([1, 2], $routine->items()->orderBy('position')->pluck('position')->all());
        $this->assertSame(
            [$exerciseA->id, $exerciseB->id],
            $routine->items()->orderBy('position')->pluck('exercise_id')->all(),
        );
    }

    public function test_mover_item_reordena_y_borrar_recompacta(): void
    {
        $user = User::factory()->create();
        $routine = Routine::factory()->for($user)->create();
        $first = RoutineItem::factory()->for($routine)->create(['position' => 1]);
        $second = RoutineItem::factory()->for($routine)->create(['position' => 2]);
        $third = RoutineItem::factory()->for($routine)->create(['position' => 3]);

        // subir el tercero
        $this->actingAs($user)
            ->patch(route('routine-items.update', $third), ['direction' => 'up'])
            ->assertRedirect();
        $this->assertSame(
            [$first->id, $third->id, $second->id],
            $routine->items()->orderBy('position')->pluck('id')->all(),
        );

        // borrar del medio recompacta 1..2
        $this->actingAs($user)
            ->delete(route('routine-items.destroy', $third))
            ->assertRedirect();
        $this->assertSame([1, 2], $routine->items()->orderBy('position')->pluck('position')->all());
    }

    public function test_editar_objetivo_de_un_item(): void
    {
        $user = User::factory()->create();
        $routine = Routine::factory()->for($user)->create();
        $item = RoutineItem::factory()->for($routine)->create(['target' => null]);

        $this->actingAs($user)
            ->patch(route('routine-items.update', $item), ['target' => '4x10'])
            ->assertRedirect();

        $this->assertSame('4x10', $item->fresh()->target);
    }

    public function test_rutina_ajena_es_invisible_para_otro_usuario(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $routine = Routine::factory()->for($owner)->has(RoutineItem::factory()->count(2), 'items')->create();

        $this->actingAs($intruder)->get(route('routines.show', $routine))->assertForbidden();
        $this->actingAs($intruder)
            ->patch(route('routines.update', $routine), ['name' => 'robada'])
            ->assertForbidden();
        $this->actingAs($intruder)
            ->delete(route('routines.destroy', $routine))
            ->assertForbidden();

        $item = $routine->items->first();
        $this->actingAs($intruder)
            ->post(route('routine-items.store', $routine), ['exercise_id' => $this->exerciseFromCatalog()->id])
            ->assertForbidden();
        $this->actingAs($intruder)
            ->delete(route('routine-items.destroy', $item))
            ->assertForbidden();
    }

    public function test_inicia_sesion_desde_rutina_y_la_show_prepara_sus_ejercicios(): void
    {
        $user = User::factory()->create();
        $routine = Routine::factory()->for($user)->has(RoutineItem::factory()->count(2), 'items')->create();

        $response = $this->actingAs($user)
            ->post(route('workout-sessions.store'), ['routine_id' => $routine->id]);

        $session = WorkoutSession::where('user_id', $user->id)->first();
        $this->assertSame($routine->id, $session->routine_id);
        $response->assertRedirect(route('workout-sessions.show', $session));

        $this->get(route('workout-sessions.show', $session))
            ->assertOk()
            ->assertInertia(fn (InertiaPage $page) => $page
                ->component('workout/Show')
                ->where('routine.name', $routine->name)
                ->has('routinePlan', 2)
                ->where('routineExerciseIds', $routine->items()->orderBy('position')->pluck('exercise_id')->all()));
    }

    public function test_sesion_por_dia_muestra_solo_el_plan_de_ese_dia(): void
    {
        $user = User::factory()->create();
        $routine = Routine::factory()->for($user)->create();
        $exerciseA = Exercise::factory()->catalog()->create();
        $exerciseB = Exercise::factory()->catalog()->create();

        // Lunes: A · Miércoles: B
        RoutineItem::factory()->for($routine)->for($exerciseA)->create(['day_of_week' => 1, 'position' => 1]);
        RoutineItem::factory()->for($routine)->for($exerciseB)->create(['day_of_week' => 3, 'position' => 1]);

        $this->actingAs($user)
            ->post(route('workout-sessions.store'), ['routine_id' => $routine->id, 'routine_day' => 3]);

        $session = WorkoutSession::where('user_id', $user->id)->first();
        $this->assertSame(3, $session->routine_day);

        $this->get(route('workout-sessions.show', $session))
            ->assertOk()
            ->assertInertia(fn (InertiaPage $page) => $page
                ->where('routine.day', 'Miércoles')
                ->has('routinePlan', 1)
                ->where('routinePlan.0.exerciseId', $exerciseB->id));
    }

    public function test_no_se_puede_iniciar_sesion_desde_rutina_ajena(): void
    {
        $user = User::factory()->create();
        $owner = User::factory()->create();
        $foreignRoutine = Routine::factory()->for($owner)->create();

        $this->actingAs($user)
            ->post(route('workout-sessions.store'), ['routine_id' => $foreignRoutine->id])
            ->assertSessionHasErrors('routine_id');

        $this->assertDatabaseMissing('workout_sessions', ['user_id' => $user->id]);
    }

    public function test_borrar_rutina_no_borra_sus_sesiones_pasadas(): void
    {
        $user = User::factory()->create();
        $routine = Routine::factory()->for($user)->create();
        $session = WorkoutSession::factory()->for($user)->create(['routine_id' => $routine->id]);

        $this->actingAs($user)->delete(route('routines.destroy', $routine))->assertRedirect();

        $this->assertNotNull($session->fresh());
        $this->assertNull($session->fresh()->routine_id);
    }
}
