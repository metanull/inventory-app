<?php

namespace App\Http\Requests\ThemeTranslation;

use Illuminate\Foundation\Http\FormRequest;

class ShowThemeTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['include' => ['sometimes', 'string']];
    }
}
