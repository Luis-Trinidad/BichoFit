<?php

namespace App\Http\Controllers\Workout;

use App\Http\Controllers\Controller;
use App\Models\WorkoutSession;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $sessions = $request->user()->workoutSessions()
            ->whereNotNull('finished_at')
            ->with('sets')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('history/Index', [
            'sessions' => $sessions->through(fn (WorkoutSession $session) => [
                'id' => $session->id,
                'date' => $session->date->toDateString(),
                'exercises' => $session->sets->unique('exercise_id')->count(),
                'sets' => $session->sets->count(),
                'volumeKg' => (float) $session->sets->sum(fn ($set) => $set->reps * $set->weight_kg),
            ])->withQueryString(),
        ]);
    }
}
