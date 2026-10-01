<?php

namespace App\Http\Requests;

use App\Models\Exercise;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoutineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => ['sometimes', 'string', 'max:100'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
            // Estado completo deseado de la rutina: el backend sincroniza
            'items' => ['sometimes', 'array'],
            'items.*.id' => ['sometimes', 'nullable', 'integer'],
            'items.*.exercise_id' => [
                'required',
                'integer',
                Rule::exists('exercises', 'id')->where(function ($query) use ($userId) {
                    $query->whereNull('user_id')->orWhere('user_id', $userId);
                }),
            ],
            'items.*.day_of_week' => ['required', 'integer', 'min:1', 'max:7'],
            'items.*.position' => ['required', 'integer', 'min:1'],
            'items.*.target' => ['sometimes', 'nullable', 'string', 'max:50'],
        ];
    }
}
