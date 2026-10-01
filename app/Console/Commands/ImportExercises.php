<?php

namespace App\Console\Commands;

use App\Models\Exercise;
use App\Support\ExerciseNameTranslator;
use Illuminate\Console\Command;

/**
 * Importa el dataset público de ejercicios (gymvisual) al catálogo global:
 * 1.3k+ ejercicios con instrucciones en español, miniatura jpg y GIF animado.
 *
 * Uso: php artisan exercises:import /ruta/al/exercises-dataset
 *
 * Idempotente: los ejercicios se identifican por source_id y el media
 * existente no se vuelve a copiar.
 */
class ImportExercises extends Command
{
    protected $signature = 'exercises:import {dataset : Ruta al repo exercises-dataset}';

    protected $description = 'Importa el dataset de ejercicios con guías visuales al catálogo';

    /** Equipamiento del dataset → español para mostrar */
    private const EQUIPMENT_ES = [
        'barbell' => 'Barra',
        'dumbbell' => 'Mancuernas',
        'cable' => 'Polea',
        'body weight' => 'Peso corporal',
        'leverage machine' => 'Máquina',
        'band' => 'Banda elástica',
        'smith machine' => 'Máquina Smith',
        'kettlebell' => 'Pesa rusa',
        'weighted' => 'Con peso',
        'stability ball' => 'Pelota de estabilidad',
        'e-z curl bar' => 'Barra Z',
        'olympic barbell' => 'Barra olímpica',
        'rope' => 'Cuerda',
        'wheel roller' => 'Rueda abdominal',
        'upper body ergometer' => 'Ergómetro de brazos',
        'sled machine' => 'Trineo',
        'stationary bike' => 'Bicicleta estática',
        'elliptical machine' => 'Elíptico',
        'skierg' => 'SkiErg',
        'assisted' => 'Asistido',
        'medicine ball' => 'Pelota medicinal',
        'exercise ball' => 'Pelota de ejercicio',
        'foam roll' => 'Rodillo de espuma',
        'roller' => 'Rodillo',
        'tire' => 'Llanta',
        'trap bar' => 'Barra hexagonal',
        'grips' => 'Agarraderas',
    ];

    public function handle(): int
    {
        $dataset = rtrim((string) $this->argument('dataset'), '/');
        $jsonPath = $dataset.'/data/exercises.json';

        if (! is_file($jsonPath)) {
            $this->error("No existe {$jsonPath}. ¿Pasaste la ruta correcta del dataset?");

            return self::FAILURE;
        }

        $records = json_decode((string) file_get_contents($jsonPath), true);
        if (! is_array($records)) {
            $this->error('exercises.json no es un JSON válido.');

            return self::FAILURE;
        }

        // Destino del media dentro del disco public (storage/app/public)
        $jpgDir = storage_path('app/public/exercises/jpg');
        $gifDir = storage_path('app/public/exercises/gif');
        @mkdir($jpgDir, 0775, true);
        @mkdir($gifDir, 0775, true);

        $created = 0;
        $updated = 0;
        $mediaCopied = 0;
        $skippedMedia = 0;
        $translator = new ExerciseNameTranslator;
        $translated = 0;

        foreach ($records as $index => $record) {
            $sourceId = (string) $record['id'];
            $category = (string) $record['category'];
            $target = $record['target'] ?? null;
            $instructions = $record['instruction_steps']['es'] ?? null;
            $description = $record['instructions']['es'] ?? null;
            [$nameEs, $nameEn] = $translator->translate((string) $record['name']);
            if ($nameEs !== $nameEn) {
                $translated++;
            }

            $imagePath = $this->copyMedia(
                $dataset, $record['image'] ?? null, $jpgDir, $sourceId.'.jpg', $mediaCopied, $skippedMedia
            );
            $gifPath = $this->copyMedia(
                $dataset, $record['gif_url'] ?? null, $gifDir, $sourceId.'.gif', $mediaCopied, $skippedMedia
            );

            $exercise = Exercise::updateOrCreate(
                ['source_id' => $sourceId],
                [
                    'name' => $nameEs,
                    'name_en' => $nameEn,
                    'muscle_group' => $this->mapGroup($category, $target),
                    'user_id' => null,
                    'description_es' => $description,
                    'instructions_es' => $instructions,
                    'equipment' => $this->mapEquipment($record['equipment'] ?? null),
                    'target' => $target,
                    'secondary_muscles' => $record['secondary_muscles'] ?? null,
                    'image_path' => $imagePath,
                    'gif_path' => $gifPath,
                    'attribution' => $record['attribution'] ?? null,
                ],
            );

            $exercise->wasRecentlyCreated ? $created++ : $updated++;

            if (($index + 1) % 200 === 0) {
                $this->info(sprintf('  %d / %d…', $index + 1, count($records)));
            }
        }

        $this->info(sprintf(
            'Listo: %d creados, %d actualizados, %d traducidos al español, %d medios copiados (%d ya existían).',
            $created, $updated, $translated, $mediaCopied, $skippedMedia
        ));

        return self::SUCCESS;
    }

    /** Copia un archivo del dataset al disco public y devuelve su ruta relativa. */
    private function copyMedia(
        string $dataset,
        ?string $relative,
        string $destDir,
        string $destName,
        int &$copied,
        int &$skipped,
    ): ?string {
        if (blank($relative)) {
            return null;
        }

        $source = $dataset.'/'.$relative;
        if (! is_file($source)) {
            return null;
        }

        $dest = $destDir.'/'.$destName;
        if (is_file($dest)) {
            $skipped++;

            return 'exercises/'.basename($destDir).'/'.$destName;
        }

        copy($source, $dest);
        $copied++;

        return 'exercises/'.basename($destDir).'/'.$destName;
    }

    /** category + target del dataset → grupo muscular en español de la app. */
    private function mapGroup(string $category, ?string $target): string
    {
        $target = strtolower((string) $target);

        return match ($category) {
            'chest' => 'Pecho',
            'back' => 'Espalda',
            'shoulders' => 'Hombros',
            'upper arms' => str_contains($target, 'triceps') ? 'Tríceps' : 'Bíceps',
            'lower arms' => 'Antebrazo',
            'upper legs' => str_contains($target, 'glute') ? 'Glúteos' : 'Piernas',
            'lower legs' => 'Piernas',
            'waist' => 'Core',
            'cardio' => 'Cardio',
            'neck' => 'Cuello',
            default => 'Otro',
        };
    }

    private function mapEquipment(?string $equipment): ?string
    {
        if (blank($equipment)) {
            return null;
        }

        $key = strtolower(trim($equipment));

        return self::EQUIPMENT_ES[$key] ?? $equipment;
    }
}
