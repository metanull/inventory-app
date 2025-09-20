<?php

namespace App\Http\Requests\Country;

use Illuminate\Foundation\Http\FormRequest;

class StoreCountryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|string|size:3|unique:countries,id',
            'internal_name' => 'required|string',
            'backward_compatibility' => 'nullable|string|size:2',
        ];
    }

    /**
     * Configure the validator instance to reject unexpected parameters.
     */
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

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'id.required' => 'The country ID is required.',
            'id.size' => 'The country ID must be exactly 3 characters (ISO 3166-1 alpha-3).',
            'id.unique' => 'A country with this ID already exists.',
            'internal_name.required' => 'The internal name is required.',
            'backward_compatibility.size' => 'The backward compatibility field must be exactly 2 characters.',
        ];
    }
}
