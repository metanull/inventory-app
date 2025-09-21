<?php

namespace App\Http\Requests\Markdown;

use App\Rules\MarkdownRule;
use Illuminate\Foundation\Http\FormRequest;

class MarkdownToHtmlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'markdown' => ['required', 'string', new MarkdownRule],
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
            'markdown.required' => 'The markdown field is required.',
            'markdown.string' => 'The markdown field must be a string.',
        ];
    }
}
