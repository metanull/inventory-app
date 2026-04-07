<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateItemDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => ['prohibited'],
            'item_id' => ['prohibited'],
            'language_id' => ['sometimes', 'nullable', 'string', 'size:3', 'exists:languages,id'],
            'original_name' => ['sometimes', 'string', 'max:255'],
            'mime_type' => ['sometimes', 'string', 'max:100', 'regex:/^[a-zA-Z0-9][a-zA-Z0-9!#$&\-\^_]*\/[a-zA-Z0-9][a-zA-Z0-9!#$&\-\^_.]*$/'],
            'size' => ['sometimes', 'integer', 'min:1'],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'display_order' => ['sometimes', 'integer', 'min:0'],
            'extra' => ['sometimes', 'nullable', 'json'],
            'backward_compatibility' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
