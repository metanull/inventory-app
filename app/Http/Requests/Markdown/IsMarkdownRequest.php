<?php

namespace App\Http\Requests\Markdown;

use Illuminate\Foundation\Http\FormRequest;

class IsMarkdownRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => [
                'required',
                'string',
                'max:65535',
            ],
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
            'content.required' => 'The content field is required.',
            'content.string' => 'The content field must be a string.',
            'content.max' => 'The content field must not exceed 65535 characters.',
        ];
    }
}
