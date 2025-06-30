<?php

namespace App\Http\Requests;

use App\Rules\MarkdownRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Base request class for validating Markdown content
 *
 * This class provides common validation rules for Markdown fields
 * and can be extended by other request classes that need Markdown validation.
 */
class MarkdownRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Markdown validation doesn't require special authorization
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'markdown' => ['required', 'string', new MarkdownRule],
        ];
    }

    /**
     * Get validation rules for a specific Markdown field
     *
     * @param  string  $fieldName  The name of the field containing Markdown
     * @param  bool  $required  Whether the field is required
     * @return array The validation rules for the field
     */
    public static function getMarkdownFieldRules(string $fieldName, bool $required = true): array
    {
        $rules = ['string', new MarkdownRule];

        if ($required) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }

        return [$fieldName => $rules];
    }

    /**
     * Get validation rules for multiple Markdown fields
     *
     * @param  array  $fields  Array of field names or field => required pairs
     * @return array The validation rules for all fields
     */
    public static function getMultipleMarkdownFieldRules(array $fields): array
    {
        $rules = [];

        foreach ($fields as $field => $required) {
            // If numeric key, field name is the value and it's required by default
            if (is_numeric($field)) {
                $field = $required;
                $required = true;
            }

            $rules = array_merge($rules, self::getMarkdownFieldRules($field, $required));
        }

        return $rules;
    }

    /**
     * Get validation rules for HTML-to-Markdown conversion
     *
     * @param  string  $fieldName  The name of the field containing HTML
     * @param  bool  $required  Whether the field is required
     * @return array The validation rules for the field
     */
    public static function getHtmlFieldRules(string $fieldName, bool $required = true): array
    {
        $rules = [
            'string',
            'max:65535', // TEXT field limit
            function ($attribute, $value, $fail) {
                $markdownService = app(\App\Services\MarkdownService::class);
                try {
                    $markdownService->validateHtml($value);
                } catch (\Illuminate\Validation\ValidationException $e) {
                    $errors = $e->validator->errors()->all();
                    foreach ($errors as $error) {
                        $fail($error);
                    }
                }
            },
        ];

        if ($required) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }

        return [$fieldName => $rules];
    }

    /**
     * Get custom error messages for Markdown validation
     *
     * @return array Custom error messages
     */
    public function messages(): array
    {
        return [
            'markdown.required' => 'Markdown content is required.',
            'markdown.string' => 'Markdown content must be a string.',
            '*.required' => 'This field is required.',
            '*.string' => 'This field must be a string.',
            '*.max' => 'This field is too long (maximum :max characters).',
        ];
    }

    /**
     * Get nice attribute names for validation errors
     *
     * @return array Attribute names
     */
    public function attributes(): array
    {
        return [
            'markdown' => 'markdown content',
            'html' => 'HTML content',
            'description' => 'description',
            'notes' => 'notes',
            'content' => 'content',
        ];
    }
}
