<?php

namespace Tests\Unit;

use App\Support\BodyScanTextParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BodyScanTextParserTest extends TestCase
{
    private BodyScanTextParser $parser;

    protected function setUp(): void
    {
        $this->parser = new BodyScanTextParser;
    }

    public static function snapshots(): array
    {
        return [
            'español en una línea' => [
                "Peso 74.5 kg\nGrasa corporal 18.2 %\nMúsculo 55.1 kg\nAgua corporal 57.4 %\nIMC 23.5\nGrasa visceral 8\nEdad metabólica 22",
                [
                    'weight_kg' => 74.5,
                    'body_fat_pct' => 18.2,
                    'muscle_mass_kg' => 55.1,
                    'water_pct' => 57.4,
                    'bmi' => 23.5,
                    'visceral_fat' => 8.0,
                    'metabolic_age' => 22.0,
                ],
            ],
            'inglés' => [
                "Weight 80.0 kg\nBody fat 21.3 %\nMuscle mass 58.2 kg\nBody water 55.9 %\nBMI 25.1\nVisceral fat 10\nMetabolic age 28",
                [
                    'weight_kg' => 80.0,
                    'body_fat_pct' => 21.3,
                    'muscle_mass_kg' => 58.2,
                    'water_pct' => 55.9,
                    'bmi' => 25.1,
                    'visceral_fat' => 10.0,
                    'metabolic_age' => 28.0,
                ],
            ],
            'etiqueta y valor en líneas separadas' => [
                "Peso\n74,5 kg\nGrasa corporal\n18,2 %",
                [
                    'weight_kg' => 74.5,
                    'body_fat_pct' => 18.2,
                ],
            ],
            'con acentos y ruido' => [
                "Mi composición\nPeso: 92,3 kg\nMasa muscular: 68.4 kg\nEdad metabólica 31 años",
                [
                    'weight_kg' => 92.3,
                    'muscle_mass_kg' => 68.4,
                    'metabolic_age' => 31.0,
                ],
            ],
            'fuera de rango se descarta' => [
                "Peso 4321 kg\nIMC 950\nAgua corporal 55 %",
                [
                    'water_pct' => 55.0,
                ],
            ],
            'OCR real en columnas (etiquetas y valores separados)' => [
                "Mi Bascula\n\nPeso\n\nGrasa corporal\n\nMasa muscular\n\nAgua corporal\n\nIMC\n\nGrasa visceral\n\nEdad metabolica\n\n74.5 kg\n\n18.2 %\n\n55.1 kg\n\n57.4 Y\n\n23.5\n\n22 anos",
                [
                    'weight_kg' => 74.5,
                    'body_fat_pct' => 18.2,
                    'muscle_mass_kg' => 55.1,
                    'water_pct' => 57.4,
                    'bmi' => 23.5,
                    'metabolic_age' => 22.0,
                ],
            ],
            'sin nada reconocible' => [
                'Hola mundo 123',
                [],
            ],
        ];
    }

    #[DataProvider('snapshots')]
    public function test_extrae_los_campos_esperados(string $ocrText, array $expected): void
    {
        $result = $this->parser->parse($ocrText);

        $detected = collect($result)->map(fn ($item) => $item['value'])->all();
        ksort($detected);
        ksort($expected);

        $this->assertSame(
            $expected,
            $detected,
            "No coinciden los campos detectados para: {$ocrText}",
        );
    }

    public function test_extras_de_okok_international(): void
    {
        $text = "Protein\n16.4 %\nBMR\n1642\nSubcutaneous Fat\n15.3 %";

        $extras = $this->parser->parseExtras($text);

        $expected = ['proteina' => 16.4, 'bmr' => 1642.0, 'subcutaneous_fat' => 15.3];
        ksort($expected);
        ksort($extras);
        $this->assertSame($expected, $extras);
    }

    public function test_confianza_alta_cuando_el_numero_esta_en_la_misma_linea(): void
    {
        $result = $this->parser->parse("Peso 70 kg\nGrasa corporal\n15 %");

        $this->assertGreaterThanOrEqual(0.9, $result['weight_kg']['confidence']);
        $this->assertLessThan(0.9, $result['body_fat_pct']['confidence']);
    }
}
