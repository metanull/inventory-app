<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreItemMediaRequest extends FormRequest
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
            'language_id' => ['nullable', 'string', 'size:3', 'exists:languages,id'],
            'type' => ['required', 'in:audio,video'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'url' => ['required', 'string', 'max:512', 'url'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'extra' => ['nullable', 'json'],
            'backward_compatibility' => ['nullable', 'string'],
        ];
    }
}
