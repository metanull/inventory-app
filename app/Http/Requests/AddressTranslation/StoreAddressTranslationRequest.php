<?php

namespace App\Http\Requests\AddressTranslation;

use Illuminate\Foundation\Http\FormRequest;

class StoreAddressTranslationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'address_id' => ['required', 'uuid', 'exists:addresses,id'],
            'language_id' => ['required', 'string', 'size:3', 'exists:languages,id'],
            'address' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'backward_compatibility' => ['nullable', 'string', 'max:255'],
        ];
    }
}
