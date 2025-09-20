<?php

namespace App\Http\Requests\Picture;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AttachPictureRequest extends FormRequest
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
            'available_image_id' => 'required|uuid|exists:available_images,id',
            'internal_name' => 'required|string|max:255',
            'backward_compatibility' => 'nullable|string|max:255',
            'copyright_text' => 'nullable|string|max:1000',
            'copyright_url' => 'nullable|url|max:255',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $allowedParameters = [
                'available_image_id', 'internal_name', 'backward_compatibility',
                'copyright_text', 'copyright_url',
            ];
            $receivedParameters = array_keys($this->all());
            $unexpectedParameters = array_diff($receivedParameters, $allowedParameters);

            foreach ($unexpectedParameters as $parameter) {
                $validator->errors()->add($parameter, "The {$parameter} parameter is not allowed.");
            }
        });
    }
}
