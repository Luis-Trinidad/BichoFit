<?php

namespace App\Models;

use Database\Factories\RoutineFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'notes'])]
class Routine extends Model
{
    /** @use HasFactory<RoutineFactory> */
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RoutineItem::class)->orderBy('position');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(WorkoutSession::class);
    }

    /**
     * Sincroniza los items con el estado deseado en una transacción:
     * borra los ausentes, actualiza los que traen id y crea los nuevos.
     *
     * @param array<int, array{id?: int|null, exercise_id: int, day_of_week: int, position: int, target?: string|null}> $items
     */
    public function syncItems(array $items): void
    {
        $this->getConnection()->transaction(function () use ($items) {
            $wantedIds = collect($items)->pluck('id')->filter()->all();

            $this->items()
                ->when($wantedIds !== [], fn ($query) => $query->whereNotIn('id', $wantedIds))
                ->delete();

            foreach ($items as $item) {
                $attributes = [
                    'exercise_id' => $item['exercise_id'],
                    'day_of_week' => $item['day_of_week'],
                    'position' => $item['position'],
                    'target' => $item['target'] ?? null,
                ];

                if (! empty($item['id'])) {
                    $this->items()->whereKey($item['id'])->update($attributes);
                } else {
                    $this->items()->create($attributes);
                }
            }
        });
    }
}
