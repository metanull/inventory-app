<?php

namespace App\Http\Requests\Collection;

use Illuminate\Foundation\Http\FormRequest;

class StoreCollectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'internal_name' => 'required|string|max:255|unique:collections,internal_name',
            'language_id' => 'required|string|size:3|exists:languages,id',
            'context_id' => 'required|string|exists:contexts,id',
            'backward_compatibility' => 'nullable|string|max:255',
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
            'internal_name.required' => 'The internal name is required.',
            'internal_name.unique' => 'The internal name must be unique.',
            'language_id.required' => 'A language must be selected.',
            'language_id.size' => 'The language ID must be exactly 3 characters.',
            'language_id.exists' => 'The selected language is invalid.',
            'context_id.required' => 'A context must be selected.',
            'context_id.exists' => 'The selected context is invalid.',
        ];
    }
}
