<?php

namespace App\Http\Requests\Api;

use App\Support\Pagination\PaginationParams;
use Illuminate\Foundation\Http\FormRequest;

class IndexTagForItemRequest extends FormRequest
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
        ];
    }

    /**
     * Get validated pagination parameters.
     *
     * @return array{page:int, per_page:int}
     */
    public function getPaginationParams(): array
    {
        return PaginationParams::fromRequest($this);
    }
}
