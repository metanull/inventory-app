<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreItemItemLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var \App\Models\Item|null $item */
        $item = $this->route('item');
        $sourceItemId = $item?->id;

        return [
            'target_id' => [
                'required',
                'uuid',
                'exists:items,id',
                Rule::notIn([$sourceItemId]), // Prevent self-links
            ],
            'context_id' => [
                'required',
                'uuid',
                'exists:contexts,id',
                Rule::unique('item_item_links')
                    ->where('source_id', $sourceItemId)
                    ->where('target_id', $this->input('target_id')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'target_id.not_in' => 'An item cannot link to itself.',
            'context_id.unique' => 'A link already exists between these items in this context.',
        ];
    }
}
