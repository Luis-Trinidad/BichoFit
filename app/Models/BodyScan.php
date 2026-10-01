<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Medición de composición corporal (báscula de bioimpedancia).
 *
 * @property int $id
 * @property int $user_id
 * @property Carbon $scanned_at
 * @property float|null $weight_kg
 * @property float|null $body_fat_pct
 * @property float|null $muscle_mass_kg
 * @property float|null $water_pct
 * @property float|null $bone_mass_kg
 * @property float|null $bmi
 * @property int|null $visceral_fat
 * @property int|null $metabolic_age
 * @property array|null $extra
 * @property string|null $image_path
 * @property string $source
 */
#[Fillable([
    'scanned_at', 'weight_kg', 'body_fat_pct', 'muscle_mass_kg', 'water_pct',
    'bone_mass_kg', 'bmi', 'visceral_fat', 'metabolic_age', 'extra',
    'image_path', 'source',
])]
class BodyScan extends Model
{
    /** @use HasFactory<\Database\Factories\BodyScanFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'scanned_at' => 'date',
            'extra' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
