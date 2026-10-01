<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoutineRequest;
use App\Models\Exercise;
use App\Models\Routine;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoutineController extends Controller
{
    public function index(Request $request)
    {
        $routines = $request->user()->routines()
            ->withCount('items')
            ->with('items.exercise')
            ->orderBy('name')
            ->get();

        return Inertia::render('routines/Index', [
            'routines' => $routines->map(fn (Routine $routine) => [
                'id' => $routine->id,
                'name' => $routine->name,
                'notes' => $routine->notes,
                'exercises' => $routine->items->pluck('exercise.name')->filter()->values()->all(),
            ])->all(),
        ]);
    }

    public function store(StoreRoutineRequest $request)
    {
        $routine = $request->user()->routines()->create($request->validated());

        return redirect()->route('routines.show', $routine);
    }

    public function show(Request $request, Routine $routine)
    {
        $this->authorize('view', $routine);

        $routine->load('items.exercise');

        return Inertia::render('routines/Show', [
            'routine' => [
                'id' => $routine->id,
                'name' => $routine->name,
                'notes' => $routine->notes,
                'items' => $routine->items->map(fn ($item) => [
                    'id' => $item->id,
                    'exerciseId' => $item->exercise_id,
                    'name' => $item->exercise->name,
                    'muscleGroup' => $item->exercise->muscle_group,
                    'equipment' => $item->exercise->equipment,
                    'imageUrl' => $item->exercise->imageUrl(),
                    'target' => $item->target,
                ])->all(),
            ],
            'exercises' => Exercise::forUser($request->user()->id)
                ->orderBy('name')
                ->get(['id', 'name', 'muscle_group', 'image_path'])
                ->map(fn (Exercise $exercise) => [
                    'id' => $exercise->id,
                    'name' => $exercise->name,
                    'muscle_group' => $exercise->muscle_group,
                    'imageUrl' => $exercise->imageUrl(),
                ])->all(),
        ]);
    }

    public function update(StoreRoutineRequest $request, Routine $routine)
    {
        $this->authorize('update', $routine);

        $routine->update($request->validated());

        return back();
    }

    public function destroy(Request $request, Routine $routine)
    {
        $this->authorize('delete', $routine);

        $routine->delete();

        return redirect()->route('routines.index')->with('status', 'routine-deleted');
    }
}
