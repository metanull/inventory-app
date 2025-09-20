<?php

namespace App\Http\Requests\Province;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateProvinceRequest extends FormRequest
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
        $provinceId = $this->route('province');

        return [
            'internal_name' => 'string|unique:provinces,internal_name,'.$provinceId,
            'country_id' => 'exists:countries,id',
            'backward_compatibility' => 'nullable|string',
            'translations' => 'array|min:1',
            'translations.*.language_id' => 'required_with:translations|exists:languages,id',
            'translations.*.name' => 'required_with:translations|string',
            'include' => 'string|in:translations',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $allowedParameters = ['internal_name', 'country_id', 'backward_compatibility', 'translations', 'include'];
            $receivedParameters = array_keys($this->all());
            $unexpectedParameters = array_diff($receivedParameters, $allowedParameters);

            foreach ($unexpectedParameters as $parameter) {
                $validator->errors()->add($parameter, "The {$parameter} parameter is not allowed.");
            }
        });
    }
}
