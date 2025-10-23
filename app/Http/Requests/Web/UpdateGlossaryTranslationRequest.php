<?php

namespace App\Http\Requests\Web;

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
            'language_id' => ['required', 'string', 'max:10', 'exists:languages,id'],
            'definition' => ['required', 'string'],
        ];
    }
}
