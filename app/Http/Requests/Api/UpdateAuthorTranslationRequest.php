<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAuthorTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $uniqueRule = Rule::unique('author_translations')
            ->where('author_id', $this->input('author_id'))
            ->where('language_id', $this->input('language_id'))
            ->where('context_id', $this->input('context_id'))
            ->ignore($this->route('authorTranslation'));

        return [
            'id' => ['prohibited'],
            'author_id' => ['required', 'uuid', 'exists:authors,id', $uniqueRule],
            'language_id' => ['required', 'string', 'size:3', 'exists:languages,id'],
            'context_id' => ['required', 'uuid', 'exists:contexts,id'],
            'curriculum' => ['nullable', 'string'],
            'backward_compatibility' => ['nullable', 'string', 'max:255'],
            'extra' => ['nullable', 'array'],
        ];
    }
}
