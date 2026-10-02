<?php

use App\Http\Controllers\BodyScanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\RoutineController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\VersionController;
use App\Http\Controllers\RoutineItemController;
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

    Route::get('body-scans', [BodyScanController::class, 'index'])->name('body-scans.index');
    Route::post('body-scans/ocr', [BodyScanController::class, 'ocr'])->name('body-scans.ocr');
    Route::post('body-scans', [BodyScanController::class, 'store'])->name('body-scans.store');
    Route::delete('body-scans/{scan}', [BodyScanController::class, 'destroy'])
        ->whereNumber('scan')->name('body-scans.destroy');

    Route::get('version', VersionController::class)->name('version');

    Route::get('progress', ProgressController::class)->name('progress.show');
    Route::get('progress/{exercise}', ProgressController::class)->whereNumber('exercise')->name('progress.exercise');

    Route::get('exercises', [ExerciseController::class, 'index'])->name('exercises.index');
    Route::get('exercises/{exercise}', [ExerciseController::class, 'show'])
        ->whereNumber('exercise')->name('exercises.show');
    Route::post('exercises', [ExerciseController::class, 'store'])->name('exercises.store');
    Route::delete('exercises/{exercise}', [ExerciseController::class, 'destroy'])
        ->whereNumber('exercise')->name('exercises.destroy');

    Route::get('routines', [RoutineController::class, 'index'])->name('routines.index');
    Route::post('routines', [RoutineController::class, 'store'])->name('routines.store');
    Route::get('routines/{routine}', [RoutineController::class, 'show'])
        ->whereNumber('routine')->name('routines.show');
    Route::patch('routines/{routine}', [RoutineController::class, 'update'])
        ->whereNumber('routine')->name('routines.update');
    Route::delete('routines/{routine}', [RoutineController::class, 'destroy'])
        ->whereNumber('routine')->name('routines.destroy');

    Route::post('routines/{routine}/items', [RoutineItemController::class, 'store'])
        ->whereNumber('routine')->name('routine-items.store');
    Route::patch('routine-items/{item}', [RoutineItemController::class, 'update'])
        ->whereNumber('item')->name('routine-items.update');
    Route::delete('routine-items/{item}', [RoutineItemController::class, 'destroy'])
        ->whereNumber('item')->name('routine-items.destroy');
});
