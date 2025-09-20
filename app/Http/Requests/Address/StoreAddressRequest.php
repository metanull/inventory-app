<?php

namespace App\Http\Requests\Address;

use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'prohibited',
            'internal_name' => 'required|string|unique:addresses,internal_name',
            'country_id' => 'required|exists:countries,id',
            'backward_compatibility' => 'nullable|string',
            'translations' => 'array|min:1',
            'translations.*.language_id' => 'required|exists:languages,id',
            'translations.*.address' => 'required|string',
            'translations.*.description' => 'nullable|string',
            'include' => 'string',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $allowedKeys = array_keys($this->rules());
            $inputKeys = array_keys($this->all());
            $unexpectedKeys = array_diff($inputKeys, $allowedKeys);

            if (! empty($unexpectedKeys)) {
                foreach ($unexpectedKeys as $key) {
                    $validator->errors()->add($key, "The {$key} field is not allowed.");
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'id.prohibited' => 'The ID field cannot be set manually.',
            'internal_name.required' => 'The internal name is required.',
            'internal_name.unique' => 'The internal name must be unique.',
            'country_id.required' => 'A country must be selected.',
            'country_id.exists' => 'The selected country is invalid.',
            'translations.required' => 'At least one translation is required.',
            'translations.min' => 'At least one translation is required.',
            'translations.*.language_id.required' => 'A language is required for each translation.',
            'translations.*.language_id.exists' => 'The selected language is invalid.',
            'translations.*.address.required' => 'The address text is required for each translation.',
        ];
    }
}
