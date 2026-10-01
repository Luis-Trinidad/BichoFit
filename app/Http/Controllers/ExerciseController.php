<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExerciseRequest;
use App\Models\Exercise;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class ExerciseController extends Controller
{
    public function index(Request $request)
    {
        $exercises = Exercise::forUser($request->user()->id)
            ->orderBy('muscle_group')
            ->orderBy('name')
            ->get(['id', 'name', 'muscle_group', 'user_id']);

        $grouped = $exercises
            ->groupBy('muscle_group')
            ->map(fn ($group) => $group->map(fn (Exercise $exercise) => [
                'id' => $exercise->id,
                'name' => $exercise->name,
                'isCustom' => $exercise->user_id !== null,
            ])->all());

        return Inertia::render('exercises/Index', [
            'groups' => Arr::sortRecursive($grouped->all()),
        ]);
    }

    public function store(StoreExerciseRequest $request)
    {
        $exercise = $request->user()->exercises()->create($request->validated());

        return redirect()->route('exercises.index')->with('status', 'exercise-created');
    }

    public function destroy(Request $request, Exercise $exercise)
    {
        $this->authorize('delete', $exercise);

        $exercise->delete();

        return back()->with('status', 'exercise-deleted');
    }
}
