<?php

namespace App\Http\Requests\Contact;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'internal_name' => 'required|string|unique:contacts,internal_name',
            'phone_number' => 'nullable|string|regex:/^\+?[0-9\s\-\(\)]+$/',
            'fax_number' => 'nullable|string|regex:/^\+?[0-9\s\-\(\)]+$/',
            'email' => 'nullable|email',
            'backward_compatibility' => 'nullable|string',
            'translations' => 'required|array|min:1',
            'translations.*.language_id' => 'required|exists:languages,id',
            'translations.*.label' => 'required|string',
            'include' => 'sometimes|string',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $allowedParameters = ['internal_name', 'phone_number', 'fax_number', 'email', 'backward_compatibility', 'translations', 'include'];
            $receivedParameters = array_keys($this->all());
            $unexpectedParameters = array_diff($receivedParameters, $allowedParameters);

            foreach ($unexpectedParameters as $parameter) {
                $validator->errors()->add($parameter, "The {$parameter} parameter is not allowed.");
            }
        });
    }
}
