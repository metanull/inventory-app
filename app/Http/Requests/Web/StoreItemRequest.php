<?php

namespace App\Http\Requests\Web;

use App\Models\Item;
use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null; // Additional policy checks can be added later
    }

    public function rules(): array
    {
        return [
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
        if ($type === Item::TYPE_OBJECT && $parentId !== null) {
            $validator->errors()->add('parent_id', 'Items of type "object" should not have a parent.');
        }

        if ($type === Item::TYPE_MONUMENT && $parentId !== null) {
            $validator->errors()->add('parent_id', 'Items of type "monument" should not have a parent.');
        }

        if ($type === Item::TYPE_DETAIL && $parentId === null) {
            $validator->errors()->add('parent_id', 'Items of type "detail" must have a parent of type "object" or "monument".');
        } elseif ($type === Item::TYPE_DETAIL && $parentId !== null) {
            $parent = Item::find($parentId);
            if ($parent && ! in_array($parent->type, [Item::TYPE_OBJECT, Item::TYPE_MONUMENT])) {
                $validator->errors()->add('parent_id', 'Items of type "detail" must have a parent of type "object" or "monument".');
            }
        }

        if ($type === Item::TYPE_PICTURE && $parentId !== null) {
            $parent = Item::find($parentId);
            if ($parent && ! in_array($parent->type, [Item::TYPE_OBJECT, Item::TYPE_MONUMENT, Item::TYPE_DETAIL])) {
                $validator->errors()->add('parent_id', 'Items of type "picture" can only have a parent of type "object", "monument", or "detail".');
            }
        }
    }
}
