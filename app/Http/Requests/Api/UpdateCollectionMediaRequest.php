<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCollectionMediaRequest extends FormRequest
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
            'collection_id' => ['prohibited'],
            'language_id' => ['sometimes', 'nullable', 'string', 'size:3', 'exists:languages,id'],
            'type' => ['sometimes', 'in:audio,video'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'url' => ['sometimes', 'string', 'max:512', 'url'],
            'display_order' => ['sometimes', 'integer', 'min:0'],
            'extra' => ['sometimes', 'nullable', 'json'],
            'backward_compatibility' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
