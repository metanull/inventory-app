<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Api\Concerns\HasPagination;
use Illuminate\Foundation\Http\FormRequest;

class IndexLanguageRequest extends FormRequest
{
    use HasPagination;

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
}
