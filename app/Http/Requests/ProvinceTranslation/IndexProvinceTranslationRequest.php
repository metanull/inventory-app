<?php

namespace App\Http\Requests\ProvinceTranslation;

use Illuminate\Foundation\Http\FormRequest;

class IndexProvinceTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'filter' => ['sometimes', 'array'],
            'filter.province_id' => ['sometimes', 'uuid', 'exists:provinces,id'],
            'filter.language_id' => ['sometimes', 'string', 'size:3', 'exists:languages,id'],
            'filter.context_id' => ['sometimes', 'uuid', 'exists:contexts,id'],
            'sort' => ['sometimes', 'string', 'in:name,description,created_at,updated_at'],
            'order' => ['sometimes', 'string', 'in:asc,desc'],
        ];
    }
}
