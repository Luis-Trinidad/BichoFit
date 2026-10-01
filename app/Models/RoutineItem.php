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
 * @property int $exercise_id
 * @property int $position
 * @property string|null $target
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['exercise_id', 'position', 'target'])]
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
}
