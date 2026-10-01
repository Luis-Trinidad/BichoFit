<?php

namespace App\Http\Controllers\Workout;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\RoutineItem;
use App\Models\WorkoutSession;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class SessionController extends Controller
{
    /** Inicia la sesión del día (opcionalmente desde una rutina) y redirige. */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'routine_id' => [
                'nullable',
                Rule::exists('routines', 'id')->where('user_id', $request->user()->id),
            ],
            'routine_day' => ['nullable', 'integer', 'min:1', 'max:7'],
        ]);

        $routineId = $validated['routine_id'] ?? null;
        $routineDay = null;

        if ($routineId !== null) {
            $routine = $request->user()->routines()->findOrFail($routineId);
            // Día pedido, o el de hoy si tiene ejercicios, o el primer día con ejercicios
            // reorder(): la relación ordena por position y DISTINCT+ORDER BY rompe en Postgres
            $daysWithItems = $routine->items()->reorder()->distinct()->pluck('day_of_week');
            $today = now()->isoWeekday();
            $routineDay = $validated['routine_day'] ?? null;

            if ($routineDay === null || ! $daysWithItems->contains($routineDay)) {
                $routineDay = $daysWithItems->contains($today) ? $today : $daysWithItems->min();
            }
        }

        $session = $request->user()->workoutSessions()->create([
            'date' => now()->toDateString(),
            'started_at' => Carbon::now(),
            'routine_id' => $routineId,
            'routine_day' => $routineDay,
        ]);

        return redirect()->route('workout-sessions.show', $session);
    }

    public function show(Request $request, WorkoutSession $session)
    {
        $this->authorize('view', $session);

        $session->load('sets.exercise', 'routine.items');

        // Plan del día de la rutina que cubre esta sesión (en orden)
        $routineExerciseIds = collect();
        $routinePlan = collect();
        if ($session->routine) {
            $dayItems = $session->routine->items
                ->when($session->routine_day !== null, fn ($items) => $items->where('day_of_week', $session->routine_day))
                ->values();
            $routinePlan = $dayItems->map(fn ($item) => [
                'exerciseId' => $item->exercise_id,
                'name' => $item->exercise->name,
                'target' => $item->target,
            ])->values();
            $routineExerciseIds = $dayItems
                ->whereNotIn('exercise_id', $session->sets->pluck('exercise_id'))
                ->pluck('exercise_id')
                ->unique()
                ->values();
        }

        // Última serie previa por ejercicio (sesiones pasadas) para pre-llenar
        // reps/peso cuando el ejercicio entra nuevo a la sesión.
        $exerciseIds = $session->sets->pluck('exercise_id')->unique();
        $lastByExercise = $request->user()->workoutSets()
            ->whereIn('exercise_id', $exerciseIds)
            ->where('session_id', '!=', $session->id)
            ->orderByDesc('id')
            ->get()
            ->unique('exercise_id')
            ->mapWithKeys(fn ($set) => [
                $set->exercise_id => ['reps' => $set->reps, 'weightKg' => (float) $set->weight_kg],
            ]);

        return Inertia::render('workout/Show', [
            'session' => [
                'id' => $session->id,
                'date' => $session->date->toDateString(),
                'startedAt' => $session->started_at->toIso8601String(),
                'finishedAt' => $session->finished_at?->toIso8601String(),
                'notes' => $session->notes,
                'sets' => $session->sets->map(fn ($set) => [
                    'id' => $set->id,
                    'exerciseId' => $set->exercise_id,
                    'exerciseName' => $set->exercise->name,
                    'muscleGroup' => $set->exercise->muscle_group,
                    'gifUrl' => $set->exercise->resolvedGifUrl(),
                    'imageUrl' => $set->exercise->resolvedImageUrl(),
                    'reps' => $set->reps,
                    'weightKg' => (float) $set->weight_kg,
                ])->all(),
            ],
            'routine' => $session->routine ? [
                'id' => $session->routine->id,
                'name' => $session->routine->name,
                'day' => $session->routine_day !== null
                    ? RoutineItem::dayName($session->routine_day)
                    : null,
            ] : null,
            'routinePlan' => $routinePlan->all(),
            'lastByExercise' => $lastByExercise->all(),
            'exercises' => Exercise::forUser($request->user()->id)
                ->orderBy('name')
                ->get(['id', 'name', 'name_en', 'muscle_group', 'image_path'])
                ->map(fn (Exercise $exercise) => [
                    'id' => $exercise->id,
                    'name' => $exercise->name,
                    'nameEn' => $exercise->name_en,
                    'muscle_group' => $exercise->muscle_group,
                    'imageUrl' => $exercise->resolvedImageUrl(),
                    'gifUrl' => $exercise->resolvedGifUrl(),
                ])->all(),
            'routineExerciseIds' => $routineExerciseIds->all(),
        ]);
    }

    public function finish(Request $request, WorkoutSession $session)
    {
        $this->authorize('update', $session);

        $session->update(['finished_at' => Carbon::now()]);

        return redirect()->route('dashboard')->with('status', 'workout-finished');
    }

    public function destroy(Request $request, WorkoutSession $session)
    {
        $this->authorize('delete', $session);

        $session->delete();

        return redirect()->route('history.index')->with('status', 'workout-deleted');
    }
}
