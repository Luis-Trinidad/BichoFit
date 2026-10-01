<?php

namespace App\Http\Requests;

use App\Models\Exercise;
use App\Models\WorkoutSession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreSetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'exercise_id' => [
                'required',
                'integer',
                // El ejercicio debe ser visible: catálogo global o propio
                Rule::exists('exercises', 'id')->where(function ($query) use ($userId) {
                    $query->whereNull('user_id')->orWhere('user_id', $userId);
                }),
            ],
            'reps' => ['required', 'integer', 'min:1', 'max:255'],
            'weight_kg' => ['required', 'numeric', 'min:0', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'exercise_id.exists' => 'El ejercicio no existe o no está disponible.',
            'weight_kg.max' => 'El peso máximo por serie es 2000 kg.',
        ];
    }

    /**
     * Sesión propia por id; 404 si es ajena (no filtrar existencia)
     * y error de validación si ya está terminada.
     */
    public function findSessionOrFail(int $sessionId): WorkoutSession
    {
        $session = WorkoutSession::where('user_id', $this->user()->id)
            ->findOrFail($sessionId);

        if ($session->finished_at !== null) {
            throw ValidationException::withMessages([
                'session' => 'Esta sesión ya está terminada.',
            ]);
        }

        return $session;
    }
}
