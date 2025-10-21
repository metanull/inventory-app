<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'internal_name' => ['required', 'string', 'max:255'],
            'country_id' => ['required', 'string', 'max:3', 'exists:countries,id'],
            'translations' => ['nullable', 'array'],
            'translations.*.language_id' => ['required_with:translations.*', 'string', 'exists:languages,id'],
            'translations.*.address' => ['required_with:translations.*', 'string'],
            'translations.*.description' => ['nullable', 'string'],
        ];
    }
}
