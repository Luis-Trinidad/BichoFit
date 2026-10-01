<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoutineItemRequest;
use App\Models\Routine;
use App\Models\RoutineItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoutineItemController extends Controller
{
    public function store(StoreRoutineItemRequest $request, Routine $routine)
    {
        $this->authorize('update', $routine);

        $nextPosition = (int) $routine->items()->max('position') + 1;

        $routine->items()->create([
            'exercise_id' => $request->validated('exercise_id'),
            'target' => $request->validated('target'),
            'position' => $nextPosition,
        ]);

        return back();
    }

    /** Edita el objetivo de la línea o mueve el ejercicio arriba/abajo. */
    public function update(Request $request, RoutineItem $item)
    {
        $this->authorize('update', $item->routine);

        $validated = $request->validate([
            'target' => ['sometimes', 'nullable', 'string', 'max:50'],
            'direction' => ['sometimes', Rule::in(['up', 'down'])],
        ]);

        if (array_key_exists('target', $validated)) {
            // '' → null: sin objetivo, no cadenas vacías
            $validated['target'] = trim((string) $validated['target']) ?: null;
            $item->update(['target' => $validated['target']]);
        }

        if (isset($validated['direction'])) {
            $this->move($item, $validated['direction']);
        }

        return back();
    }

    public function destroy(Request $request, RoutineItem $item)
    {
        $this->authorize('update', $item->routine);

        $routine = $item->routine;
        $removedPosition = $item->position;
        $item->delete();

        // Recompactar posiciones para que queden 1..n
        $routine->items()
            ->where('position', '>', $removedPosition)
            ->orderBy('position')
            ->get()
            ->each(fn (RoutineItem $later) => $later->decrement('position'));

        return back();
    }

    private function move(RoutineItem $item, string $direction): void
    {
        $isUp = $direction === 'up';
        $neighbor = RoutineItem::where('routine_id', $item->routine_id)
            ->where('position', $isUp ? '<' : '>', $item->position)
            ->orderBy('position', $isUp ? 'desc' : 'asc')
            ->first();

        if (! $neighbor) {
            return;
        }

        $itemPosition = $item->position;
        $item->update(['position' => $neighbor->position]);
        $neighbor->update(['position' => $itemPosition]);
    }
}
