<?php

namespace App\Http\Controllers;

use App\Models\RoutineItem;
use App\Models\User;
use App\Models\WorkoutSession;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $weekStart = now()->startOfWeek();

        $recentSessions = $user->workoutSessions()
            ->whereNotNull('finished_at')
            ->with('sets')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return Inertia::render('Dashboard', [
            'activeSession' => $user->workoutSessions()->active()->latest('id')->first(['id', 'started_at']),
            'week' => [
                'sessions' => $user->workoutSessions()
                    ->whereNotNull('finished_at')
                    ->where('date', '>=', $weekStart->toDateString())
                    ->count(),
                // Racha: semanas consecutivas completadas (3+ sesiones terminadas)
                'streak' => $this->streak($user),
            ],
            'recentSessions' => $recentSessions->map(fn (WorkoutSession $session) => [
                'id' => $session->id,
                'date' => $session->date->toDateString(),
                'exercises' => $session->sets->unique('exercise_id')->count(),
                'sets' => $session->sets->count(),
                'volumeKg' => (float) $session->sets->sum(fn ($set) => $set->reps * $set->weight_kg),
            ])->all(),
            'routines' => $user->routines()
                ->with('items.exercise')
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($routine) => [
                    'id' => $routine->id,
                    'name' => $routine->name,
                    'days' => $routine->items->groupBy('day_of_week')->sortKeys()->map(fn ($items, $day) => [
                        'day' => (int) $day,
                        'dayName' => RoutineItem::dayName((int) $day),
                        'items' => $items->map(fn ($item) => [
                            'name' => $item->exercise->name,
                            'target' => $item->target,
                            'imageUrl' => $item->exercise->resolvedImageUrl(),
                        ])->values()->all(),
                    ])->values()->all(),
                ])->all(),
        ]);
    }

    /**
     * Racha de semanas completadas seguidas: una semana cuenta cuando
     * tiene al menos 3 sesiones terminadas. La semana en curso entra a
     * la cadena al alcanzar las 3; hacia atrás todas deben cumplirla.
     */
    private function streak(User $user): int
    {
        $perWeek = $user->workoutSessions()
            ->whereNotNull('finished_at')
            ->get(['date'])
            ->groupBy(fn ($session) => $session->date->startOfWeek()->format('o-W'))
            ->map->count();

        $cursor = now()->startOfWeek();
        $streak = 0;

        // La semana actual rompe la cadena solo cuando ya pasó y no se completó
        if (($perWeek[$cursor->format('o-W')] ?? 0) < 3) {
            $cursor = $cursor->subWeek();

            if (($perWeek[$cursor->format('o-W')] ?? 0) < 3) {
                return 0;
            }
        }

        while (($perWeek[$cursor->format('o-W')] ?? 0) >= 3) {
            $streak++;
            $cursor = $cursor->subWeek();
        }

        return $streak;
    }
}
