<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreGlossarySpellingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'id' => ['prohibited'],
            'glossary_id' => ['required', 'uuid', 'exists:glossaries,id'],
            'language_id' => ['required', 'string', 'size:3', 'exists:languages,id'],
            'spelling' => ['required', 'string'],
        ];
    }
}
