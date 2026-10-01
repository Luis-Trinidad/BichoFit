<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reps' => ['sometimes', 'integer', 'min:1', 'max:255'],
            'weight_kg' => ['sometimes', 'numeric', 'min:0', 'max:2000'],
        ];
    }
}
