<?php

namespace App\Http\Requests\Markdown;

use App\Services\MarkdownService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class HtmlToMarkdownRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'html' => [
                'required',
                'string',
                'max:65535',
                function ($attribute, $value, $fail) {
                    try {
                        app(MarkdownService::class)->validateHtml($value);
                    } catch (ValidationException $e) {
                        $errors = $e->validator->errors()->all();
                        foreach ($errors as $error) {
                            $fail($error);
                        }
                    }
                },
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
            'html.required' => 'The html field is required.',
            'html.string' => 'The html field must be a string.',
            'html.max' => 'The html field must not exceed 65535 characters.',
        ];
    }
}
