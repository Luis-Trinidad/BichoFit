<?php

namespace App\Http\Requests;

use App\Models\Exercise;
use Illuminate\Foundation\Http\FormRequest;

class StoreExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $duplicate = Exercise::forUser($this->user()->id)
                        ->whereRaw('lower(name) = lower(?)', [$value])
                        ->exists();

                    if ($duplicate) {
                        $fail('Ya existe un ejercicio con ese nombre.');
                    }
                },
            ],
            'muscle_group' => ['required', 'string', 'max:100'],
        ];
    }
}
