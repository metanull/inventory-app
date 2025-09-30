<?php

namespace App\Http\Requests\Api;

use App\Models\Item;
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

        // Only validate if type or parent_id is being updated
        if (! $this->has('type') && ! $this->has('parent_id')) {
            return;
        }

        // Get current item to check existing values
        $item = $this->route('item');
        $currentType = $type ?? $item->type;
        $currentParentId = $this->has('parent_id') ? $parentId : $item->parent_id;

        // Business rules for hierarchical relationships
        if (in_array($currentType, [Item::TYPE_OBJECT, Item::TYPE_MONUMENT]) && $currentParentId !== null) {
            $validator->errors()->add('parent_id', 'Items of type "object" or "monument" should not have a parent.');
        }

        if ($currentType === Item::TYPE_DETAIL && $currentParentId === null) {
            $validator->errors()->add('parent_id', 'Items of type "detail" must have a parent of type "object" or "monument".');
        } elseif ($currentType === Item::TYPE_DETAIL && $currentParentId !== null) {
            $parent = Item::find($currentParentId);
            if ($parent && ! in_array($parent->type, [Item::TYPE_OBJECT, Item::TYPE_MONUMENT])) {
                $validator->errors()->add('parent_id', 'Items of type "detail" must have a parent of type "object" or "monument".');
            }
        }

        if ($currentType === Item::TYPE_PICTURE && $currentParentId !== null) {
            $parent = Item::find($currentParentId);
            if ($parent && ! in_array($parent->type, [Item::TYPE_OBJECT, Item::TYPE_MONUMENT, Item::TYPE_DETAIL])) {
                $validator->errors()->add('parent_id', 'Items of type "picture" can only have a parent of type "object", "monument", or "detail".');
            }
        }

        // Prevent circular references
        if ($currentParentId !== null && $currentParentId === $item->id) {
            $validator->errors()->add('parent_id', 'An item cannot be its own parent.');
        }
    }
}
