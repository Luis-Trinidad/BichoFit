<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\Workout\HistoryController;
use App\Http\Controllers\Workout\SessionController;
use App\Http\Controllers\Workout\SetController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::post('workout-sessions', [SessionController::class, 'store'])->name('workout-sessions.store');
    Route::get('workout-sessions/{session}', [SessionController::class, 'show'])
        ->whereNumber('session')->name('workout-sessions.show');
    Route::post('workout-sessions/{session}/finish', [SessionController::class, 'finish'])
        ->whereNumber('session')->name('workout-sessions.finish');
    Route::delete('workout-sessions/{session}', [SessionController::class, 'destroy'])
        ->whereNumber('session')->name('workout-sessions.destroy');

    Route::post('workout-sessions/{session}/sets', [SetController::class, 'store'])
        ->whereNumber('session')->name('workout-sets.store');
    Route::patch('workout-sets/{set}', [SetController::class, 'update'])
        ->whereNumber('set')->name('workout-sets.update');
    Route::delete('workout-sets/{set}', [SetController::class, 'destroy'])
        ->whereNumber('set')->name('workout-sets.destroy');

    Route::get('history', [HistoryController::class, 'index'])->name('history.index');

    Route::get('exercises', [ExerciseController::class, 'index'])->name('exercises.index');
    Route::post('exercises', [ExerciseController::class, 'store'])->name('exercises.store');
    Route::delete('exercises/{exercise}', [ExerciseController::class, 'destroy'])
        ->whereNumber('exercise')->name('exercises.destroy');
});
