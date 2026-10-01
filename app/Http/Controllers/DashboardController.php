<?php

namespace App\Http\Controllers;

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
                // Racha: días consecutivos con sesión terminada (termina hoy o ayer)
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
                    'items' => $routine->items->map(fn ($item) => [
                        'name' => $item->exercise->name,
                        'target' => $item->target,
                        'imageUrl' => $item->exercise->resolvedImageUrl(),
                    ])->all(),
                ])->all(),
        ]);
    }

    /** Días consecutivos con sesión terminada; la cadena puede terminar hoy o ayer. */
    private function streak(User $user): int
    {
        $trainedDays = $user->workoutSessions()
            ->whereNotNull('finished_at')
            ->distinct()
            ->pluck('date')
            ->map(fn ($date) => $date->toDateString())
            ->flip();

        $cursor = today();
        if (! $trainedDays->has($cursor->toDateString())) {
            $cursor = $cursor->subDay();

            if (! $trainedDays->has($cursor->toDateString())) {
                return 0;
            }
        }

        $streak = 0;
        while ($trainedDays->has($cursor->toDateString())) {
            $streak++;
            $cursor = $cursor->subDay();
        }

        return $streak;
    }
}
