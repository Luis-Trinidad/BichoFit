<?php

namespace Database\Seeders;

use Illuminate\Console\View\Components\Info;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Process;

/**
 * Descarga y descomprime el dataset público de ejercicios con guías
 * visuales (gymvisual). Corre automáticamente con db:seed en producción:
 * si el media ya está descargado, no hace nada (idempotente).
 *
 * JSON embebido: database/seeders/data/exercises.json
 * Media: se descarga del repo remoto la primera vez (137 MB comprimido).
 */
class ExerciseDatasetSeeder extends Seeder
{
    use WithoutModelEvents;

    private const DATASET_REPO = 'https://github.com/hasaneyldrm/exercises-dataset';
    private const ARCHIVE_URL = self::DATASET_REPO.'/archive/refs/heads/main.tar.gz';
    private const CACHE_DIR = 'app/exercises-dataset-cache';

    public function run(): void
    {
        $jsonPath = __DIR__.'/data/exercises.json';

        if (! is_file($jsonPath)) {
            $this->command?->warn('Sin dataset embebido, saltando.');

            return;
        }

        $records = json_decode((string) file_get_contents($jsonPath), true);
        if (! is_array($records)) {
            $this->command?->warn('Dataset JSON inválido, saltando.');

            return;
        }

        // 1. Ejercicios: importar siempre (idempotente por source_id)
        $this->importExercises($records);

        // 2. Media (GIFs/miniaturas): descargar del repo remoto si falta
        $this->ensureMedia($records);
    }

    private function importExercises(array $records): void
    {
        $translator = new \App\Support\ExerciseNameTranslator;
        $created = 0;
        $updated = 0;

        foreach ($records as $record) {
            $sourceId = (string) $record['id'];
            [$nameEs, $nameEn] = $translator->translate((string) $record['name']);

            $exercise = \App\Models\Exercise::updateOrCreate(
                ['source_id' => $sourceId],
                [
                    'name' => $nameEs,
                    'name_en' => $nameEn,
                    'muscle_group' => $this->mapGroup((string) $record['category'], $record['target'] ?? null),
                    'user_id' => null,
                    'description_es' => $record['instructions']['es'] ?? null,
                    'instructions_es' => $record['instruction_steps']['es'] ?? null,
                    'equipment' => $this->mapEquipment($record['equipment'] ?? null),
                    'target' => $record['target'] ?? null,
                    'secondary_muscles' => $record['secondary_muscles'] ?? null,
                    'image_path' => 'exercises/jpg/'.$sourceId.'.jpg',
                    'gif_path' => 'exercises/gif/'.$sourceId.'.gif',
                    'attribution' => $record['attribution'] ?? null,
                ],
            );

            $exercise->wasRecentlyCreated ? $created++ : $updated++;
        }

        $this->command?->info("Dataset: {$created} creados, {$updated} actualizados.");
    }

    /**
     * Garantiza el media en storage/app/public/exercises/{jpg,gif}.
     * Si ya está (hay >= 1000 archivos), no hace nada. Si falta, descarga
     * el repo remoto comprimido y extrae solo lo necesario.
     */
    private function ensureMedia(array $records): void
    {
        $jpgDir = storage_path('app/public/exercises/jpg');
        $gifDir = storage_path('app/public/exercises/gif');
        @mkdir($jpgDir, 0775, true);
        @mkdir($gifDir, 0775, true);

        $jpgs = glob($jpgDir.'/*.jpg') ?: [];
        $gifs = glob($gifDir.'/*.gif') ?: [];

        // ¿Ya hay suficientes? No hacer nada.
        if (count($jpgs) >= 1000 && count($gifs) >= 1000) {
            $this->command?->info('Media del dataset ya descargado (idempotente).');

            return;
        }

        $this->command?->info('Descargando media del dataset (primera vez, ~140 MB)…');

        $cachePath = storage_path(self::CACHE_DIR);
        @mkdir($cachePath, 0775, true);
        $tarball = $cachePath.'/dataset.tar.gz';

        // Descargar si no está cacheado (streaming: no carga 140MB en memoria)
        if (! is_file($tarball) || filesize($tarball) < 1000) {
            try {
                Http::sink($tarball)->timeout(900)->get(self::ARCHIVE_URL);
            } catch (\Throwable $e) {
                $this->command?->warn('No se pudo descargar el media: '.$e->getMessage());

                return;
            }
            if (! is_file($tarball) || filesize($tarball) < 1000) {
                $this->command?->warn('Descarga incompleta del media. Reintenta con db:seed.');

                return;
            }
            $this->command?->info('Media descargado ('.round(filesize($tarball) / 1048576).' MB).');
        }

        // Extraer en memoria los archivos necesarios (solo los del dataset)
        $extracted = 0;
        foreach ($records as $record) {
            $sourceId = (string) $record['id'];
            $imageFile = basename((string) ($record['image'] ?? ''));
            $gifFile = basename((string) ($record['gif_url'] ?? ''));

            if ($imageFile && ! is_file($jpgDir.'/'.$sourceId.'.jpg')) {
                $this->extractFromTarball($tarball, 'images/'.$imageFile, $jpgDir.'/'.$sourceId.'.jpg') && $extracted++;
            }

            if ($gifFile && ! is_file($gifDir.'/'.$sourceId.'.gif')) {
                $this->extractFromTarball($tarball, 'videos/'.$gifFile, $gifDir.'/'.$sourceId.'.gif') && $extracted++;
            }
        }

        // Limpiar caché (ya no la necesitamos)
        @unlink($tarball);

        $this->command?->info("Media extraído: {$extracted} archivos.");
    }

    /** Extrae un archivo individual del tar.gz del dataset. */
    private function extractFromTarball(string $tarball, string $innerPath, string $destination): bool
    {
        // tar -xzf archivo.tar.gz -O ruta/interna > destino
        $result = Process::run([
            'tar', '-xzf', $tarball, '-O', 'exercises-dataset-main/'.$innerPath,
        ]);

        if ($result->failed() || $result->output() === '') {
            return false;
        }

        return file_put_contents($destination, $result->output()) !== false;
    }

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

        return [
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
        ][$key] ?? $equipment;
    }
}
