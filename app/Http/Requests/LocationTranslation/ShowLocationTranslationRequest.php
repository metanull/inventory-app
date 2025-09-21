<?php

namespace App\Http\Requests\LocationTranslation;

use Illuminate\Foundation\Http\FormRequest;

class ShowLocationTranslationRequest extends FormRequest
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
