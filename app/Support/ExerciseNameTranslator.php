<?php

namespace App\Support;

/**
 * Traduce nombres de ejercicios del dataset (inglés) a español.
 *
 * El nombre se descompone: [equipo] [calificadores] [núcleo] y se
 * reconstruye en español: núcleo + calificadores + equipo.
 * El diccionario de núcleos cubre el 100 % del dataset (969 entradas);
 * si un núcleo no está, se devuelve el nombre original.
 */
class ExerciseNameTranslator
{
    /** Prefijo de equipo → frase en español (sufijo) */
    private const EQUIPMENT = [
        'olympic barbell' => 'con barra olímpica',
        'barbell' => 'con barra',
        'e-z bar' => 'con barra Z',
        'ez bar' => 'con barra Z',
        'ez-bar' => 'con barra Z',
        'dumbbell' => 'con mancuernas',
        'kettlebell' => 'con pesa rusa',
        'cable' => 'en polea',
        'band' => 'con banda',
        'leverage machine' => 'en máquina',
        'smith machine' => 'en Smith',
        'weighted' => 'lastrado',
        'body weight' => 'a peso corporal',
        'bodyweight' => 'a peso corporal',
        'stability ball' => 'con pelota',
        'exercise ball' => 'con pelota',
        'medicine ball' => 'con pelota medicinal',
        'trap bar' => 'con barra hexagonal',
    ];

    /** Calificadores iniciales → español (se anteponen al equipo) */
    private const QUALIFIERS = [
        'assisted' => 'asistido',
        'seated' => 'sentado',
        'standing' => 'de pie',
        'sitted' => 'sentado',
        'lying' => 'acostado',
        'incline' => 'inclinado',
        'decline' => 'declinado',
        'close-grip' => 'agarre cerrado',
        'close grip' => 'agarre cerrado',
        'wide-grip' => 'agarre abierto',
        'wide grip' => 'agarre abierto',
        'reverse-grip' => 'agarre invertido',
        'reverse grip' => 'agarre invertido',
        'reverse' => 'invertido',
        'underhand' => 'supino',
        'overhand' => 'prono',
        'one arm' => 'unilateral',
        'one-arm' => 'unilateral',
        'single arm' => 'unilateral',
        'one leg' => 'unilateral',
        'one-leg' => 'unilateral',
        'single leg' => 'unilateral',
        'alternate' => 'alterno',
        'alternating' => 'alterno',
        'bent-over' => 'inclinado',
        'bent over' => 'inclinado',
        'kneeling' => 'de rodillas',
        'prone' => 'boca abajo',
        'supine' => 'boca arriba',
        'narrow stance' => 'base estrecha',
        'wide' => 'abierto',
    ];

    /** @var array<string, string> */
    private array $dictionary;

    public function __construct()
    {
        $this->dictionary = json_decode(
            (string) file_get_contents(database_path('data/exercise-names-es.json')),
            true,
        ) ?? [];
    }

    /** Devuelve [nombre_es, nombre_original] — si no hay traducción, ambos iguales. */
    public function translate(string $englishName): array
    {
        $name = $englishName;

        // 1) equipo inicial
        $equipment = null;
        foreach (self::EQUIPMENT as $prefix => $spanish) {
            if (str_starts_with(strtolower($name), $prefix.' ')) {
                $equipment = $spanish;
                $name = substr($name, strlen($prefix) + 1);

                break;
            }
        }

        // 2) calificadores iniciales (pueden acumularse)
        $qualifiers = [];
        $changed = true;
        while ($changed) {
            $changed = false;
            foreach (self::QUALIFIERS as $prefix => $spanish) {
                if (str_starts_with(strtolower($name), $prefix.' ')) {
                    $qualifiers[] = $spanish;
                    $name = substr($name, strlen($prefix) + 1);
                    $changed = true;

                    break;
                }
            }
        }

        // 3) núcleo en el diccionario
        $core = $this->dictionary[strtolower(trim($name))] ?? null;
        if ($core === null) {
            return [$englishName, $englishName];
        }

        // 4) reconstrucción: núcleo + calificadores + equipo
        $parts = array_filter([
            $core,
            implode(' ', array_unique($qualifiers)),
            $equipment,
        ], fn ($part) => $part !== null && $part !== '');

        return [implode(' ', $parts), $englishName];
    }
}
