<?php

namespace App\Http\Requests\ContactTranslation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contact_id' => ['sometimes', 'uuid', 'exists:contacts,id'],
            'language_id' => ['sometimes', 'string', 'size:3', 'exists:languages,id'],
            'label' => ['sometimes', 'string', 'max:255'],
            'backward_compatibility' => ['nullable', 'string', 'max:255'],
        ];
    }
}
