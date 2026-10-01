<?php

namespace Database\Seeders;

use App\Models\Exercise;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExerciseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Catálogo global de ejercicios (user_id null).
     * Nombres en español; agrupados por músculo principal.
     */
    public function run(): void
    {
        $catalog = [
            'Pecho' => [
                'Press de banca con barra',
                'Press de banca con mancuernas',
                'Press inclinado con barra',
                'Press inclinado con mancuernas',
                'Press declinado con barra',
                'Aperturas con mancuernas',
                'Cruce de poleas (crossover)',
                'Aperturas en polea',
                'Fondos en paralelas (pecho)',
                'Press de pecho en máquina',
                'Pull-over con mancuerna',
            ],
            'Espalda' => [
                'Dominadas',
                'Jalón al pecho',
                'Jalón agarre neutro',
                'Remo con barra',
                'Remo con mancuerna',
                'Remo en máquina',
                'Remo en polea baja',
                'Pull-over en polea alta',
                'Peso muerto convencional',
                'Hiperextensiones',
                'Face pull',
            ],
            'Hombros' => [
                'Press militar de pie',
                'Press de hombro con mancuernas',
                'Press Arnold',
                'Elevaciones laterales',
                'Elevaciones frontales',
                'Pájaro (deltoides posterior)',
                'Press de hombro en máquina',
                'Remo al cuello (upright row)',
            ],
            'Bíceps' => [
                'Curl con barra',
                'Curl alterno con mancuernas',
                'Curl martillo',
                'Curl predicador',
                'Curl en polea baja',
                'Curl concentrado',
                'Curl con barra Z',
            ],
            'Tríceps' => [
                'Press francés',
                'Extensión de tríceps en polea',
                'Fondos en banco',
                'Patada de tríceps',
                'Press de banca agarre cerrado',
                'Extensión sobre cabeza con mancuerna',
            ],
            'Piernas' => [
                'Sentadilla libre',
                'Sentadilla hack',
                'Prensa de piernas',
                'Zancadas con mancuernas',
                'Extensión de cuádriceps',
                'Curl femoral',
                'Peso muerto rumano',
                'Sentadilla búlgara',
                'Gemelos de pie',
                'Gemelos sentado',
            ],
            'Glúteos' => [
                'Hip thrust',
                'Puente de glúteo',
                'Patada de glúteo en máquina',
                'Abducción de cadera',
            ],
            'Core' => [
                'Plancha',
                'Crunch abdominal',
                'Elevación de piernas colgado',
                'Rueda abdominal',
                'Crunch en polea',
                'Russian twist',
            ],
            'Cardio' => [
                'Caminadora',
                'Bicicleta estática',
                'Elíptico',
                'Saltar la cuerda',
            ],
        ];

        foreach ($catalog as $muscleGroup => $exercises) {
            foreach ($exercises as $name) {
                Exercise::firstOrCreate(
                    ['name' => $name],
                    ['muscle_group' => $muscleGroup, 'user_id' => null],
                );
            }
        }
    }
}
