<?php

namespace App\Http\Requests\AvailableImage;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAvailableImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'prohibited',
            'path' => 'prohibited',
            'comment' => 'nullable|string',
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
            'id.prohibited' => 'The ID field cannot be set manually.',
            'path.prohibited' => 'The path field cannot be set manually.',
        ];
    }
}
