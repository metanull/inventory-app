<?php

namespace App\Http\Requests\ItemTranslation;

use Illuminate\Foundation\Http\FormRequest;

class IndexItemTranslationRequest extends FormRequest
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
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'include' => ['nullable', 'string', 'regex:/^[a-zA-Z_,]+$/'],
            'item_id' => ['nullable', 'uuid', 'exists:items,id'],
            'language_id' => ['nullable', 'string', 'size:3', 'exists:languages,id'],
            'context_id' => ['nullable', 'uuid', 'exists:contexts,id'],
        ];
    }
}
