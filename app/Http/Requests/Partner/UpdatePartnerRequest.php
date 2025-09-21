<?php

namespace App\Http\Requests\Partner;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'prohibited',
            'internal_name' => 'required|string',
            'backward_compatibility' => 'nullable|string',
            'type' => 'required|in:museum,institution,individual',
            'country_id' => 'nullable|string|size:3|exists:countries,id',
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
            'id.prohibited' => 'The partner ID cannot be modified.',
            'internal_name.required' => 'The internal name is required.',
            'type.required' => 'The partner type is required.',
            'type.in' => 'The partner type must be: museum, institution, or individual.',
            'country_id.size' => 'The country ID must be exactly 3 characters (ISO 3166-1 alpha-3).',
        ];
    }
}
