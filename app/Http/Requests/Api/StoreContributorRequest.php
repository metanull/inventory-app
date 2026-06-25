<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreContributorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => ['prohibited'],
            'collection_id' => ['required', 'uuid', 'exists:collections,id'],
            'category' => ['required', 'string', 'max:50'],
            'display_order' => ['sometimes', 'integer', 'min:0'],
            'visible' => ['sometimes', 'boolean'],
            'backward_compatibility' => ['nullable', 'string'],
            'internal_name' => ['required', 'string'],
        ];
    }
}
