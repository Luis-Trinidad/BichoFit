<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkoutSession;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProgressController extends Controller
{
    public function __invoke(Request $request, ?int $exerciseId = null)
    {
        /** @var User $user */
        $user = $request->user();

        // Volumen semanal (reps × peso) de las últimas 10 semanas — array plano
        // para poder acumular por referencia
        $weeks = [];
        foreach (range(0, 9) as $i) {
            $start = now()->startOfWeek()->subWeeks(9 - $i);
            $weeks[$start->format('o-W')] = [
                'label' => $start->translatedFormat('d M'),
                'volume' => 0.0,
            ];
        }

        $user->workoutSessions()
            ->whereNotNull('finished_at')
            ->where('date', '>=', now()->startOfWeek()->subWeeks(9))
            ->with('sets')
            ->get()
            ->each(function (WorkoutSession $session) use (&$weeks) {
                $key = $session->date->startOfWeek()->format('o-W');
                if (array_key_exists($key, $weeks)) {
                    $volume = $session->sets->sum(fn ($set) => $set->reps * $set->weight_kg);
                    $weeks[$key]['volume'] += (float) $volume;
                }
            });

        // Progresión por ejercicio: peso máximo y 1RM estimado (Epley) por sesión
        $exerciseProgression = $user->workoutSets()
            ->whereHas('session', fn ($q) => $q->whereNotNull('finished_at'))
            ->with('session:id,date', 'exercise:id,name')
            ->get()
            ->groupBy('exercise_id')
            ->map(function ($sets, $exerciseId) {
                $bySession = $sets
                    ->groupBy('session_id')
                    ->map(fn ($sessionSets) => [
                        'date' => $sessionSets->first()->session->date->toDateString(),
                        'topWeight' => (float) $sessionSets->max('weight_kg'),
                        'best1Rm' => (float) $sessionSets
                            ->map(fn ($set) => $set->weight_kg * (1 + $set->reps / 30))
                            ->max(),
                    ])
                    ->values();

                return [
                    'exerciseId' => $exerciseId,
                    'name' => $sets->first()->exercise->name,
                    'sessions' => $sets->unique('session_id')->count(),
                    'best1Rm' => $bySession->max('best1Rm'),
                    'prWeight' => $bySession->max('topWeight'),
                    'series' => $bySession->all(),
                ];
            })
            ->sortByDesc('sessions')
            ->values();

        $selected = $exerciseId
            ? $exerciseProgression->firstWhere('exerciseId', $exerciseId)
            : $exerciseProgression->first();

        // Composición corporal (báscula): serie por medición
        $bodyComp = $user->bodyScans()
            ->orderBy('scanned_at')
            ->get(['scanned_at', 'weight_kg', 'body_fat_pct', 'muscle_mass_kg', 'water_pct'])
            ->map(fn ($scan) => [
                'date' => $scan->scanned_at->toDateString(),
                'weightKg' => $scan->weight_kg !== null ? (float) $scan->weight_kg : null,
                'bodyFatPct' => $scan->body_fat_pct !== null ? (float) $scan->body_fat_pct : null,
                'muscleMassKg' => $scan->muscle_mass_kg !== null ? (float) $scan->muscle_mass_kg : null,
                'waterPct' => $scan->water_pct !== null ? (float) $scan->water_pct : null,
            ])
            ->values()
            ->all();

        return Inertia::render('progress/Index', [
            'bodyComp' => $bodyComp,
            'weeklyVolume' => array_values($weeks),
            'exercises' => $exerciseProgression
                ->map(fn ($exercise) => [
                    'exerciseId' => $exercise['exerciseId'],
                    'name' => $exercise['name'],
                    'sessions' => $exercise['sessions'],
                    'prWeight' => $exercise['prWeight'],
                    'best1Rm' => $exercise['best1Rm'],
                ])
                ->take(30)
                ->all(),
            'selected' => $selected,
        ]);
    }
}
