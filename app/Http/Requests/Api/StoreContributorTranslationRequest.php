<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreContributorTranslationRequest extends FormRequest
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
            'contributor_id' => ['required', 'uuid', 'exists:contributors,id'],
            'language_id' => ['required', 'string', 'size:3', 'exists:languages,id'],
            'context_id' => ['required', 'uuid', 'exists:contexts,id'],
            'name' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'link' => ['nullable', 'string', 'max:512'],
            'alt_text' => ['nullable', 'string'],
            'extra' => ['nullable', 'json'],
            'backward_compatibility' => ['nullable', 'string'],
        ];
    }
}
