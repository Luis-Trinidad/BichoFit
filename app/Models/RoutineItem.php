<?php

namespace App\Models;

use Database\Factories\RoutineItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $routine_id
 * @property int $day_of_week
 * @property int $exercise_id
 * @property int $position
 * @property string|null $target
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['exercise_id', 'day_of_week', 'position', 'target'])]
class RoutineItem extends Model
{
    /** @use HasFactory<RoutineItemFactory> */
    use HasFactory;

    public function routine(): BelongsTo
    {
        return $this->belongsTo(Routine::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    /** Nombre del día (ISO 1=Lunes…7=Domingo). */
    public static function dayName(int $day): string
    {
        return ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'][$day - 1] ?? "Día {$day}";
    }
}
