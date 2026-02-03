<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateItemRequest extends FormRequest
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
            'internal_name' => ['sometimes', 'required', 'string', 'max:255'],
            'backward_compatibility' => ['sometimes', 'nullable', 'string', 'max:255'],
            'type' => ['sometimes', 'required', 'in:object,monument,detail,picture'],
            'parent_id' => ['sometimes', 'nullable', 'uuid', 'exists:items,id'],
            'country_id' => ['sometimes', 'nullable', 'string', 'size:3', 'exists:countries,id'],
            'partner_id' => ['sometimes', 'nullable', 'uuid', 'exists:partners,id'],
            'project_id' => ['sometimes', 'nullable', 'uuid', 'exists:projects,id'],
            'collection_id' => ['sometimes', 'nullable', 'uuid', 'exists:collections,id'],
            'owner_reference' => ['sometimes', 'nullable', 'string', 'max:255'],
            'mwnf_reference' => ['sometimes', 'nullable', 'string', 'max:255'],
            'start_date' => ['sometimes', 'nullable', 'integer', 'min:-9999', 'max:9999'],
            'end_date' => ['sometimes', 'nullable', 'integer', 'min:-9999', 'max:9999', 'gte:start_date'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Prevent circular references
            $item = $this->route('item');
            $parentId = $this->input('parent_id');

            if ($this->has('parent_id') && $parentId !== null && $parentId === $item->id) {
                $validator->errors()->add('parent_id', 'An item cannot be its own parent.');
            }
        });
    }
}
