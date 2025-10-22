<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGlossaryTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'id' => ['prohibited'],
            'glossary_id' => ['sometimes', 'uuid', 'exists:glossaries,id'],
            'language_id' => ['sometimes', 'string', 'size:3', 'exists:languages,id'],
            'definition' => ['sometimes', 'string'],
        ];
    }
}
