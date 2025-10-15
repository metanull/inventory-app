<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'internal_name' => ['required', 'string', 'unique:contacts,internal_name'],
            'phone_number' => ['nullable', 'string', 'regex:/^\+?[0-9\s\-\(\)]+$/'],
            'fax_number' => ['nullable', 'string', 'regex:/^\+?[0-9\s\-\(\)]+$/'],
            'email' => ['nullable', 'email'],
            'backward_compatibility' => ['nullable', 'string'],
            'translations' => ['required', 'array', 'min:1'],
            'translations.*.language_id' => ['required', 'exists:languages,id'],
            'translations.*.label' => ['required', 'string'],
        ];
    }
}
