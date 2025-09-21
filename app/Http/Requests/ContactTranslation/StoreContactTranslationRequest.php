<?php

namespace App\Http\Requests\ContactTranslation;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contact_id' => ['required', 'uuid', 'exists:contacts,id'],
            'language_id' => ['required', 'string', 'size:3', 'exists:languages,id'],
            'label' => ['required', 'string', 'max:255'],
            'backward_compatibility' => ['nullable', 'string', 'max:255'],
        ];
    }
}
