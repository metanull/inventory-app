<?php

namespace App\Http\Requests\ThemeTranslation;

use Illuminate\Foundation\Http\FormRequest;

class StoreThemeTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'theme_id' => ['required', 'uuid', 'exists:themes,id'],
            'language_id' => ['required', 'string', 'size:3', 'exists:languages,id'],
            'context_id' => ['required', 'uuid', 'exists:contexts,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'introduction' => ['required', 'string'],
            'backward_compatibility' => ['nullable', 'string', 'max:255'],
            'extra' => ['nullable', 'json'],
        ];
    }
}
