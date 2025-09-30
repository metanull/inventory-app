<?php

namespace App\Http\Requests\Api;

use App\Models\Item;
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

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateHierarchicalRules($validator);
        });
    }

    /**
     * Validate hierarchical business rules.
     */
    protected function validateHierarchicalRules($validator)
    {
        $type = $this->input('type');
        $parentId = $this->input('parent_id');

        // Business rules for hierarchical relationships
        if (in_array($type, [Item::TYPE_OBJECT, Item::TYPE_MONUMENT]) && $parentId !== null) {
            $validator->errors()->add('parent_id', 'Items of type "object" or "monument" should not have a parent.');
        }

        if ($type === Item::TYPE_DETAIL && $parentId === null) {
            $validator->errors()->add('parent_id', 'Items of type "detail" must have a parent of type "object" or "monument".');
        } elseif ($type === Item::TYPE_DETAIL && $parentId !== null) {
            $parent = Item::find($parentId);
            if ($parent && ! in_array($parent->type, [Item::TYPE_OBJECT, Item::TYPE_MONUMENT])) {
                $validator->errors()->add('parent_id', 'Items of type "detail" must have a parent of type "object" or "monument".');
            }
        }

        if ($type === Item::TYPE_PICTURE && $parentId === null) {
            $validator->errors()->add('parent_id', 'Items of type "picture" must have a parent of type "object", "monument", or "detail".');
        } elseif ($type === Item::TYPE_PICTURE && $parentId !== null) {
            $parent = Item::find($parentId);
            if ($parent && ! in_array($parent->type, [Item::TYPE_OBJECT, Item::TYPE_MONUMENT, Item::TYPE_DETAIL])) {
                $validator->errors()->add('parent_id', 'Items of type "picture" can only have a parent of type "object", "monument", or "detail".');
            }
        }
    }
}
