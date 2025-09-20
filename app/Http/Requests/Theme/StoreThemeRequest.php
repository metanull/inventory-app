<?php

namespace App\Http\Requests\Theme;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreThemeRequest extends FormRequest
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
            'exhibition_id' => 'required|uuid|exists:exhibitions,id',
            'parent_id' => 'nullable|uuid|exists:themes,id',
            'internal_name' => 'required|string|unique:themes,internal_name,NULL,id,exhibition_id,'.$this->input('exhibition_id'),
            'backward_compatibility' => 'nullable|string',
            'include' => 'string|in:translations,subthemes,subthemes.translations',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $allowedParameters = ['exhibition_id', 'parent_id', 'internal_name', 'backward_compatibility', 'include'];
            $receivedParameters = array_keys($this->all());
            $unexpectedParameters = array_diff($receivedParameters, $allowedParameters);

            foreach ($unexpectedParameters as $parameter) {
                $validator->errors()->add($parameter, "The {$parameter} parameter is not allowed.");
            }
        });
    }
}
