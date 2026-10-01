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
                'volumeKg' => (float) $user->workoutSets()
                    ->whereHas('session', fn ($q) => $q
                        ->where('user_id', $user->id)
                        ->where('date', '>=', $weekStart->toDateString()))
                    ->selectRaw('COALESCE(SUM(reps * weight_kg), 0) as aggregate')
                    ->value('aggregate'),
            ],
            'recentSessions' => $recentSessions->map(fn (WorkoutSession $session) => [
                'id' => $session->id,
                'date' => $session->date->toDateString(),
                'exercises' => $session->sets->unique('exercise_id')->count(),
                'sets' => $session->sets->count(),
                'volumeKg' => (float) $session->sets->sum(fn ($set) => $set->reps * $set->weight_kg),
            ])->all(),
        ]);
    }
}
