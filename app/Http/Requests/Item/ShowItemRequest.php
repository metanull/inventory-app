<?php

namespace App\Http\Requests\Item;

use Illuminate\Foundation\Http\FormRequest;

class ShowItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'include' => 'sometimes|string',
            'with' => 'sometimes|string',
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
            'with.string' => 'The with parameter must be a string.',
        ];
    }
}
