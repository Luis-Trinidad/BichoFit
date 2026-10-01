<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoutineItemRequest extends FormRequest
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
                Rule::exists('exercises', 'id')->where(function ($query) use ($userId) {
                    $query->whereNull('user_id')->orWhere('user_id', $userId);
                }),
            ],
            'day_of_week' => ['sometimes', 'integer', 'min:1', 'max:7'],
            'target' => ['sometimes', 'nullable', 'string', 'max:50'],
        ];
    }
}
