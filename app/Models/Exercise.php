<?php

namespace App\Models;

use Database\Factories\ExerciseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $muscle_group
 * @property int|null $user_id
 * @property string|null $source_id
 * @property string|null $description_es
 * @property array<int, string>|null $instructions_es
 * @property string|null $equipment
 * @property string|null $target
 * @property array<int, string>|null $secondary_muscles
 * @property string|null $image_path
 * @property string|null $gif_path
 * @property string|null $attribution
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'name', 'name_en', 'muscle_group', 'user_id', 'source_id', 'description_es',
    'instructions_es', 'equipment', 'target', 'secondary_muscles',
    'image_path', 'gif_path', 'attribution',
])]
class Exercise extends Model
{
    /** @use HasFactory<ExerciseFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'instructions_es' => 'array',
            'secondary_muscles' => 'array',
        ];
    }

    /** Ejercicios visibles para un usuario: catálogo global + los propios. */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where(fn (Builder $q) => $q
            ->whereNull('user_id')
            ->orWhere('user_id', $userId)
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workoutSets(): HasMany
    {
        return $this->hasMany(WorkoutSet::class);
    }

    public function routineItems(): HasMany
    {
        return $this->hasMany(RoutineItem::class);
    }

    /** URL pública de la miniatura (jpg) o null. */
    public function imageUrl(): ?string
    {
        return $this->image_path ? '/storage/'.$this->image_path : null;
    }

    /** URL pública del GIF animado o null. */
    public function gifUrl(): ?string
    {
        return $this->gif_path ? '/storage/'.$this->gif_path : null;
    }
}
