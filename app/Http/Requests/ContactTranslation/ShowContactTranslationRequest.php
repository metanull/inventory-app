<?php

namespace App\Http\Requests\ContactTranslation;

use Illuminate\Foundation\Http\FormRequest;

class ShowContactTranslationRequest extends FormRequest
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
