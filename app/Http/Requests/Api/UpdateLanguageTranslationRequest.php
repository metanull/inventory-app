<?php

namespace App\Http\Requests\Api;

use App\Models\LanguageTranslation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLanguageTranslationRequest extends FormRequest
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
        /** @var LanguageTranslation|null $languageTranslation */
        $languageTranslation = $this->route('languageTranslation');

        $uniqueRule = Rule::unique('language_translations')
            ->where('language_id', $this->input('language_id'))
            ->where('display_language_id', $this->input('display_language_id'))
            ->ignore($languageTranslation->id);

        return [
            'id' => ['prohibited'],
            'language_id' => ['sometimes', 'string', 'size:3', 'exists:languages,id', $uniqueRule],
            'display_language_id' => ['sometimes', 'string', 'size:3', 'exists:languages,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'backward_compatibility' => ['nullable', 'string', 'max:255'],
            'extra' => ['nullable', 'array'],
        ];
    }
}
