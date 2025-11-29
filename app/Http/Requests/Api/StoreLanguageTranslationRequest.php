<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLanguageTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $uniqueRule = Rule::unique('language_translations')
            ->where('language_id', $this->input('language_id'))
            ->where('display_language_id', $this->input('display_language_id'));

        return [
            'id' => ['prohibited'],
            'language_id' => ['required', 'string', 'size:3', 'exists:languages,id', $uniqueRule],
            'display_language_id' => ['required', 'string', 'size:3', 'exists:languages,id'],
            'name' => ['required', 'string', 'max:255'],
            'backward_compatibility' => ['nullable', 'string', 'max:255'],
            'extra' => ['nullable', 'array'],
        ];
    }
}
