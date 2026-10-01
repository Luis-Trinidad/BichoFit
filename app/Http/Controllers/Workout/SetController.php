<?php

namespace App\Http\Controllers\Workout;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSetRequest;
use App\Http\Requests\UpdateSetRequest;
use App\Models\WorkoutSet;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SetController extends Controller
{
    public function store(StoreSetRequest $request, int $session)
    {
        $sessionModel = $request->findSessionOrFail($session);

        $set = $sessionModel->sets()->create($request->validated());

        return back()->with('lastSetId', $set->id);
    }

    public function update(UpdateSetRequest $request, WorkoutSet $set)
    {
        $this->authorize('update', $set->session);

        $set->update($request->validated());

        return back();
    }

    public function destroy(Request $request, WorkoutSet $set)
    {
        $this->authorize('update', $set->session);

        $set->delete();

        return back();
    }
}
