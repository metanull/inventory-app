<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
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
            'id' => ['prohibited'],
            'internal_name' => ['required', 'string', 'max:255'],
            'backward_compatibility' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:object,monument,detail,picture'],
            'parent_id' => ['nullable', 'uuid', 'exists:items,id'],
            'country_id' => ['nullable', 'string', 'size:3', 'exists:countries,id'],
            'partner_id' => ['nullable', 'uuid', 'exists:partners,id'],
            'project_id' => ['nullable', 'uuid', 'exists:projects,id'],
            'collection_id' => ['nullable', 'uuid', 'exists:collections,id'],
            'owner_reference' => ['nullable', 'string', 'max:255'],
            'mwnf_reference' => ['nullable', 'string', 'max:255'],
        ];
    }
}
