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
            ->get(['id', 'name', 'name_en', 'muscle_group', 'user_id', 'equipment', 'image_path', 'gif_path']);

        $grouped = $exercises
            ->groupBy('muscle_group')
            ->map(fn ($group) => $group->map(fn (Exercise $exercise) => [
                'id' => $exercise->id,
                'name' => $exercise->name,
                'nameEn' => $exercise->name_en,
                'isCustom' => $exercise->user_id !== null,
                'equipment' => $exercise->equipment,
                'imageUrl' => $exercise->imageUrl(),
                'hasGuide' => $exercise->gif_path !== null,
            ])->all());

        return Inertia::render('exercises/Index', [
            'groups' => Arr::sortRecursive($grouped->all()),
        ]);
    }

    /** Detalle completo (guía GIF + pasos) para el diálogo de información. */
    public function show(Request $request, Exercise $exercise)
    {
        return response()->json([
            'id' => $exercise->id,
            'name' => $exercise->name,
            'muscleGroup' => $exercise->muscle_group,
            'equipment' => $exercise->equipment,
            'target' => $exercise->target,
            'secondaryMuscles' => $exercise->secondary_muscles,
            'description' => $exercise->description_es,
            'steps' => $exercise->instructions_es,
            'imageUrl' => $exercise->imageUrl(),
            'gifUrl' => $exercise->gifUrl(),
            'attribution' => $exercise->attribution,
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
