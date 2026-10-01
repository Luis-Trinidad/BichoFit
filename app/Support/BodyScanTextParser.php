<?php

namespace App\Support;

/**
 * Convierte el texto crudo del OCR de una captura de báscula en pares
 * etiqueta→valor. Diseñado para ser genérico (cualquier app de báscula,
 * español o inglés) y nunca lanzar excepciones: si no detecta, devuelve
 * vacío y el usuario llena a mano.
 */
class BodyScanTextParser
{
    /** Campos de la app → sinónimos de etiqueta (normalizados sin acentos). */
    private const LABELS = [
        'weight_kg' => ['peso', 'peso corporal', 'weight', 'body weight', 'peso kg', 'pesokg', 'kg'],
        'body_fat_pct' => ['grasa corporal', 'grasa', 'porcentaje de grasa', 'body fat', 'fat rate', 'body fat rate', 'fat', 'grasa %'],
        'muscle_mass_kg' => ['masa muscular', 'musculo', 'musculos', 'muscle', 'muscle mass', 'skeletal muscle', 'musculo esqueletico'],
        'water_pct' => ['agua corporal', 'agua', 'water rate', 'body water', 'water', 'hidratacion', 'porcentaje de agua'],
        'bone_mass_kg' => ['masa osea', 'hueso', 'bone mass', 'bone', 'masa de hueso'],
        'bmi' => ['imc', 'indice de masa corporal', 'bmi', 'indice masa corporal'],
        'visceral_fat' => ['grasa visceral', 'visceral fat index', 'visceral'],
        'metabolic_age' => ['edad metabolica', 'metabolic age', 'edad corporal', 'body age', 'physical age'],
    ];

    /** Métricas extra comunes: se guardan en el JSONB `extra`. */
    private const EXTRA_LABELS = [
        'proteina' => ['proteina', 'protein', 'porcentaje de proteina'],
        'bmr' => ['metabolismo basal', 'bmr', 'basal metabolism', 'calories', 'calorias', 'kcal'],
        'lean_mass_kg' => ['masa magra', 'peso sin grasa', 'masa libre de grasa', 'lean mass'],
        'skeletal_muscle_pct' => ['musculo esqueletico', 'skeletal muscle'],
        'subcutaneous_fat' => ['grasa subcutanea', 'subcutaneous fat', 'subcutaneous'],
        'body_score' => ['puntuacion corporal', 'body score', 'puntaje corporal'],
    ];

    /** Rango plausible por campo: fuera de rango se descarta (el OCR alucina). */
    private const RANGES = [
        'weight_kg' => [20, 400],
        'body_fat_pct' => [1, 70],
        'muscle_mass_kg' => [5, 200],
        'water_pct' => [20, 80],
        'bone_mass_kg' => [0.5, 15],
        'bmi' => [10, 60],
        'visceral_fat' => [1, 30],
        'metabolic_age' => [5, 100],
    ];

    /**
     * @return array<string, array{value: float, confidence: float}>
     *         campo → valor detectado y confianza (0-1)
     */
    public function parse(string $text): array
    {
        $lines = $this->normalizeLines($text);
        $found = [];
        $sameLine = [];

        // Pasada A: etiqueta y número en la MISMA línea (la más fiable)
        foreach (self::LABELS as $field => $synonyms) {
            foreach ($lines as $line) {
                foreach ($synonyms as $label) {
                    if ($line === '' || ! str_contains($line, $label)) {
                        continue;
                    }
                    $number = $this->numberAfterLabel($line, $label);
                    if ($number !== null && $this->inRange($field, $number)) {
                        $sameLine[$field] = true;
                        $found[$field] = ['value' => $number, 'confidence' => 0.9];

                        break 2;
                    }
                }
            }
        }

        // ¿Layout de columnas? (etiquetas y valores en bloques separados)
        $columnLayout = $this->countStandaloneLabels($lines) >= 2;

        if ($columnLayout) {
            // Pasada B: aparear por orden dentro de cada unidad (kg/%/años)
            return $this->parseByColumns($lines, $found);
        }

        // Pasada C (fallback): etiqueta con el número en las líneas siguientes
        foreach (array_keys(self::LABELS) as $field) {
            if (isset($found[$field])) {
                continue;
            }
            $number = $this->numberElsewhere($lines, $field);
            if ($number !== null && $this->inRange($field, $number)) {
                $found[$field] = ['value' => $number, 'confidence' => 0.5];
            }
        }

        return $found;
    }

    /**
     * Métricas extra detectadas (conocidas o genéricas): etiqueta → valor.
     * Las genéricas conservan su nombre para que el usuario lo vea.
     *
     * @return array<string, float>
     */
    public function parseExtras(string $text): array
    {
        $lines = $this->normalizeLines($text);
        $extras = [];

        // conocidas: número en la misma línea o en la siguiente no vacía
        foreach (self::EXTRA_LABELS as $key => $synonyms) {
            foreach ($lines as $lineIndex => $line) {
                foreach ($synonyms as $label) {
                    if (! str_contains($line, $label)) {
                        continue;
                    }
                    if (preg_match('/(\d{1,4}(?:[.,]\d{1,2})?)/u', $line, $matches)) {
                        $extras[$key] = (float) str_replace(',', '.', $matches[1]);

                        break 2;
                    }
                    // valor en la siguiente línea con texto
                    for ($next = $lineIndex + 1; $next <= $lineIndex + 2 && $next < count($lines); $next++) {
                        if (preg_match('/(\d{1,4}(?:[.,]\d{1,2})?)/u', $lines[$next], $m)) {
                            $extras[$key] = (float) str_replace(',', '.', $m[1]);

                            break 3;
                        }
                    }
                }
            }
        }

        // genéricas: "Etiqueta: 12.3" o "Etiqueta 12.3" con etiqueta desconocida
        $knownWords = collect(self::LABELS)->flatten()
            ->merge(collect(self::EXTRA_LABELS)->flatten())
            ->merge(['kg', '%', 'kcal', 'anos', 'imc', 'bmi'])
            ->unique()
            ->values()
            ->all();

        foreach ($lines as $line) {
            if (preg_match('/^([a-zñ ]{3,28})\s*:?\s+(\d{1,4}(?:[.,]\d{1,2})?)\s*(kg|%|kcal|anos|g)?$/u', $line, $matches)) {
                $label = trim($matches[1]);
                if ($label === '' || $this->touchesKnown($label, $knownWords)) {
                    continue;
                }
                $key = str_replace(' ', '_', $label);
                $extras[$key] = (float) str_replace(',', '.', $matches[2]);
                if (count($extras) >= 12) {
                    break;
                }
            }
        }

        return $extras;
    }

    /** Primer valor en una línea posterior inmediata al índice dado. */
    private function nextValueAfter(array $valueLines, int $index): ?array
    {
        foreach ($valueLines as $value) {
            if ($value['index'] > $index) {
                $distance = $value['index'] - $index;

                return $distance <= 2 ? [$value['value'], $value['unit']] : null;
            }
        }

        return null;
    }

    /** ¿La línea toca alguna etiqueta conocida? (para no duplicar campos core) */
    private function touchesKnown(string $label, array $knownWords): bool
    {
        foreach ($knownWords as $word) {
            if ($word !== '' && str_contains($label, $word)) {
                return true;
            }
        }

        return false;
    }

    private function countStandaloneLabels(array $lines): int
    {
        $count = 0;
        foreach ($lines as $line) {
            $clean = trim($line, " :.\t");
            foreach (self::LABELS as $synonyms) {
                if (in_array($clean, $synonyms, true)) {
                    $count++;

                    break;
                }
            }
        }

        return $count;
    }

    /** Unidad implícita de cada campo para el apareo por columnas. */
    private const FIELD_UNITS = [
        'weight_kg' => 'kg',
        'muscle_mass_kg' => 'kg',
        'bone_mass_kg' => 'kg',
        'body_fat_pct' => '%',
        'water_pct' => '%',
        'metabolic_age' => 'anos',
        'bmi' => '',
        'visceral_fat' => '',
    ];

    private function parseByColumns(array $lines, array $found): array
    {
        // etiquetas sueltas (la línea ES la etiqueta) en orden de aparición
        $labelLines = [];
        foreach ($lines as $index => $line) {
            $clean = trim($line, " :.\t");
            foreach (self::LABELS as $field => $synonyms) {
                if (in_array($clean, $synonyms, true) && ! isset($found[$field])) {
                    $labelLines[] = ['field' => $field, 'index' => $index];
                }
            }
        }

        // líneas que son solo un valor con unidad
        $valueLines = [];
        foreach ($lines as $index => $line) {
            if (preg_match('/^(\d{1,3}(?:[.,]\d{1,2})?)\s*(kg|kgs|%|y|°|anos|a)?$/', trim($line), $matches)) {
                $unit = $matches[2] ?? '';
                // el OCR confunde % con y/° seguido
                $unit = in_array($unit, ['a', 'anos'], true) ? 'anos' : ($unit === 'y' || $unit === '°' ? '%' : $unit);
                $valueLines[] = [
                    'index' => $index,
                    'value' => (float) str_replace(',', '.', $matches[1]),
                    'unit' => $unit,
                ];
            }
        }

        if (count($labelLines) < 2 || count($valueLines) < 2) {
            return $found;
        }

        // Layout de tarjetas (etiqueta con su valor justo debajo): el vecino
        // inmediato de la etiqueta es su valor. En layout de bloques el vecino
        // es otra etiqueta y no casa, cayendo al apareo por unidades de abajo.
        foreach ($labelLines as $label) {
            if (isset($found[$label['field']])) {
                continue;
            }
            $next = $this->nextValueAfter($valueLines, $label['index']);
            if ($next === null) {
                continue;
            }
            [$number, $unit] = $next;
            $expected = self::FIELD_UNITS[$label['field']];

            if (($unit === '' || $unit === $expected) && $this->inRange($label['field'], $number)) {
                $found[$label['field']] = ['value' => $number, 'confidence' => 0.7];
            }
        }

        // Apareo por unidades, respetando el orden dentro de cada grupo
        foreach (['kg', '%', 'anos', ''] as $unit) {
            $labels = array_values(array_filter($labelLines, fn ($l) => self::FIELD_UNITS[$l['field']] === $unit));
            $values = array_values(array_filter($valueLines, fn ($v) => $v['unit'] === $unit));

            foreach ($labels as $position => $label) {
                if (! isset($values[$position])) {
                    continue;
                }
                $candidate = $values[$position]['value'];
                if ($this->inRange($label['field'], $candidate)) {
                    $found[$label['field']] = ['value' => $candidate, 'confidence' => 0.6];
                }
            }
        }

        return $found;
    }

    /** Líneas normalizadas: minúsculas, sin acentos, sin dobles espacios. */
    private function normalizeLines(string $text): array
    {
        $normalized = mb_strtolower($text);
        $normalized = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ'],
            ['a', 'e', 'i', 'o', 'u', 'u', 'n'],
            $normalized,
        );

        return array_map(
            fn (string $line) => trim(preg_replace('/\s+/u', ' ', $line) ?? ''),
            preg_split('/\r\n|\r|\n/', $normalized) ?: [],
        );
    }

    /** Número pegado a la etiqueta en la MISMA línea: "peso 74.5 kg" / "peso: 74,5". */
    private function numberAfterLabel(string $line, string $label): ?float
    {
        $position = mb_strpos($line, $label);
        if ($position === false) {
            return null;
        }

        $rest = mb_substr($line, $position + mb_strlen($label));
        // primer número (admite coma o punto decimal) con unidad opcional
        if (preg_match('/(\d{1,3}(?:[.,]\d{1,2})?)/u', $rest, $matches)) {
            return (float) str_replace(',', '.', $matches[1]);
        }

        return null;
    }

    /**
     * Búscala alternativa: la etiqueta y el número en líneas vecinas
     * (los OCR de apps suelen separarlos: "Peso" \n "74.5 kg").
     */
    private function numberElsewhere(array $lines, string $field): ?float
    {
        foreach ($lines as $index => $line) {
            foreach (self::LABELS[$field] as $label) {
                if ($line === $label || trim($line, " :.\t") === $label) {
                    foreach (array_slice($lines, $index + 1, 2) as $neighbor) {
                        if (preg_match('/^(\d{1,3}(?:[.,]\d{1,2})?)\s*(kg|%|a|anos)?/u', trim($neighbor), $matches)) {
                            return (float) str_replace(',', '.', $matches[1]);
                        }
                    }
                }
            }
        }

        return null;
    }

    private function inRange(string $field, float $value): bool
    {
        [$min, $max] = self::RANGES[$field];

        return $value >= $min && $value <= $max;
    }
}
