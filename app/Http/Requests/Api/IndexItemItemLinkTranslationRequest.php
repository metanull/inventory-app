<?php

namespace App\Http\Requests\Api;

use App\Support\Pagination\PaginationParams;
use Illuminate\Foundation\Http\FormRequest;

class IndexItemItemLinkTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'item_item_link_id' => 'sometimes|uuid|exists:item_item_links,id',
            'language_id' => 'sometimes|string|size:3|exists:languages,id',
        ];
    }

    /**
     * Get validated pagination parameters.
     */
    public function getPaginationParams(): array
    {
        return PaginationParams::fromRequest($this);
    }
}
