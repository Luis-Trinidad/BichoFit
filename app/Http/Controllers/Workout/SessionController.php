<?php

namespace App\Http\Controllers\Workout;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\WorkoutSession;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class SessionController extends Controller
{
    /** Inicia la sesión del día y redirige a la pantalla de entrenamiento. */
    public function store(Request $request)
    {
        $session = $request->user()->workoutSessions()->create([
            'date' => now()->toDateString(),
            'started_at' => Carbon::now(),
        ]);

        return redirect()->route('workout-sessions.show', $session);
    }

    public function show(Request $request, WorkoutSession $session)
    {
        $this->authorize('view', $session);

        $session->load('sets.exercise');

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
                    'reps' => $set->reps,
                    'weightKg' => (float) $set->weight_kg,
                ])->all(),
            ],
            'lastByExercise' => $lastByExercise->all(),
            'exercises' => Exercise::forUser($request->user()->id)
                ->orderBy('name')
                ->get(['id', 'name', 'muscle_group']),
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
