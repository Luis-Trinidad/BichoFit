<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoutineRequest;
use App\Http\Requests\UpdateRoutineRequest;
use App\Models\Exercise;
use App\Models\Routine;
use App\Models\RoutineItem;
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
                'days' => $routine->items
                    ->groupBy('day_of_week')
                    ->sortKeys()
                    ->map(fn ($items, $day) => [
                        'dayName' => RoutineItem::dayName((int) $day),
                        'exercises' => $items->pluck('exercise.name')->filter()->values()->all(),
                    ])
                    ->values()
                    ->all(),
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
                    'dayOfWeek' => $item->day_of_week,
                    'name' => $item->exercise->name,
                    'muscleGroup' => $item->exercise->muscle_group,
                    'equipment' => $item->exercise->equipment,
                    'imageUrl' => $item->exercise->resolvedImageUrl(),
                    'target' => $item->target,
                ])->all(),
            ],
            'exercises' => Exercise::forUser($request->user()->id)
                ->orderBy('name')
                ->get(['id', 'name', 'name_en', 'muscle_group', 'image_path'])
                ->map(fn (Exercise $exercise) => [
                    'id' => $exercise->id,
                    'name' => $exercise->name,
                    'nameEn' => $exercise->name_en,
                    'muscle_group' => $exercise->muscle_group,
                    'imageUrl' => $exercise->resolvedImageUrl(),
                ])->all(),
        ]);
    }

    /**
     * Guardado por lotes: recibe el estado completo deseado de la rutina
     * (nombre + items con día/posición/objetivo) y lo sincroniza en una
     * transacción — borra los quitados, actualiza los existentes y crea
     * los nuevos (sin id).
     */
    public function update(UpdateRoutineRequest $request, Routine $routine)
    {
        $this->authorize('update', $routine);

        $validated = $request->validated();

        $routine->update(collect($validated)->only(['name', 'notes'])->all());

        if (array_key_exists('items', $validated)) {
            $routine->syncItems($validated['items']);
        }

        return back();
    }

    public function destroy(Request $request, Routine $routine)
    {
        $this->authorize('delete', $routine);

        $routine->delete();

        return redirect()->route('routines.index')->with('status', 'routine-deleted');
    }
}
