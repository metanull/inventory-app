<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAddressTranslationRequest extends FormRequest
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
            'address_id' => ['sometimes', 'uuid', 'exists:addresses,id'],
            'language_id' => ['sometimes', 'string', 'exists:languages,id'],
            'address' => ['sometimes', 'string'],
            'description' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
