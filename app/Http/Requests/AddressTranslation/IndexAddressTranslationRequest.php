<?php

namespace App\Http\Requests\AddressTranslation;

use Illuminate\Foundation\Http\FormRequest;

class IndexAddressTranslationRequest extends FormRequest
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
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'filter' => ['sometimes', 'array'],
            'filter.address_id' => ['sometimes', 'uuid', 'exists:addresses,id'],
            'filter.language_id' => ['sometimes', 'string', 'size:3', 'exists:languages,id'],
            'filter.context_id' => ['sometimes', 'uuid', 'exists:contexts,id'],
            'sort' => ['sometimes', 'string', 'in:name,description,created_at,updated_at'],
            'order' => ['sometimes', 'string', 'in:asc,desc'],
        ];
    }
}
