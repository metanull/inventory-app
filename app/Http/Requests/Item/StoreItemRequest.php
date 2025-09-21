<?php

namespace App\Http\Requests\Item;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'include' => 'sometimes|string',
            'id' => 'prohibited',
            'partner_id' => 'nullable|uuid|exists:partners,id',
            'internal_name' => 'required|string',
            'backward_compatibility' => 'nullable|string',
            'type' => 'required|in:object,monument',
            'country_id' => 'nullable|string|size:3|exists:countries,id',
            'project_id' => 'nullable|uuid|exists:projects,id',
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
            'id.prohibited' => 'The item ID cannot be set manually.',
            'partner_id.uuid' => 'The partner ID must be a valid UUID.',
            'partner_id.exists' => 'The selected partner does not exist.',
            'internal_name.required' => 'The internal name is required.',
            'type.required' => 'The item type is required.',
            'type.in' => 'The item type must be: object or monument.',
            'country_id.size' => 'The country ID must be exactly 3 characters (ISO 3166-1 alpha-3).',
            'country_id.exists' => 'The selected country does not exist.',
            'project_id.uuid' => 'The project ID must be a valid UUID.',
            'project_id.exists' => 'The selected project does not exist.',
        ];
    }
}
