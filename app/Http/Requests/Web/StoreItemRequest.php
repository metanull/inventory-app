<?php

namespace App\Http\Requests\Web;

use App\Enums\ItemType;
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

}
