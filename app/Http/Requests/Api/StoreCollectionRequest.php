<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreCollectionRequest extends FormRequest
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
            'internal_name' => ['required', 'string', 'max:255', 'unique:collections,internal_name'],
            'type' => ['required', 'in:collection,exhibition,gallery'],
            'language_id' => ['required', 'string', 'size:3', 'exists:languages,id'],
            'context_id' => ['required', 'string', 'exists:contexts,id'],
            'backward_compatibility' => ['nullable', 'string', 'max:255'],
        ];
    }
}
