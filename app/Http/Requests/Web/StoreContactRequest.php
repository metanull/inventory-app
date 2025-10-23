<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'internal_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:255'],
            'fax_number' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'translations' => ['nullable', 'array'],
            'translations.*.language_id' => ['required_with:translations.*', 'string', 'exists:languages,id'],
            'translations.*.label' => ['required_with:translations.*', 'string', 'max:255'],
        ];
    }
}
