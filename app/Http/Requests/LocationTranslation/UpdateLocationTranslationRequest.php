<?php

namespace App\Http\Requests\LocationTranslation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLocationTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'location_id' => ['required', 'uuid', 'exists:locations,id'],
            'language_id' => ['required', 'string', 'size:3', 'exists:languages,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'backward_compatibility' => ['nullable', 'string', 'max:255'],
        ];
    }
}
