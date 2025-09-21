<?php

namespace App\Http\Requests\ProvinceTranslation;

use Illuminate\Foundation\Http\FormRequest;

class StoreProvinceTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'province_id' => ['required', 'uuid', 'exists:provinces,id'],
            'language_id' => ['required', 'string', 'size:3', 'exists:languages,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'backward_compatibility' => ['nullable', 'string', 'max:255'],
        ];
    }
}
