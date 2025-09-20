<?php

namespace App\Http\Requests\Location;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateLocationRequest extends FormRequest
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
        $location = $this->route('location');
        $locationId = $location instanceof \App\Models\Location ? $location->id : $location;

        return [
            'internal_name' => 'string|unique:locations,internal_name,'.$locationId.',id',
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
