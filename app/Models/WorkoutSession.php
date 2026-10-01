<?php

namespace App\Models;

use Database\Factories\WorkoutSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property Carbon $date
 * @property int|null $routine_id
 * @property int|null $routine_day
 * @property string|null $notes
 * @property Carbon $started_at
 * @property Carbon|null $finished_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['date', 'routine_id', 'routine_day', 'notes', 'started_at', 'finished_at'])] class WorkoutSession extends Model
{
    /** @use HasFactory<WorkoutSessionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    /** Sesión en curso: empezada y nunca terminada. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('finished_at');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function routine(): BelongsTo
    {
        return $this->belongsTo(Routine::class);
    }

    public function sets(): HasMany
    {
        return $this->hasMany(WorkoutSet::class, 'session_id')->orderBy('id');
    }

    /** Volumen total de la sesión en kg (Σ reps × peso). */
    public function volumeKg(): float
    {
        return (float) $this->sets()
            ->selectRaw('COALESCE(SUM(reps * weight_kg), 0) as aggregate')
            ->value('aggregate');
    }
}
