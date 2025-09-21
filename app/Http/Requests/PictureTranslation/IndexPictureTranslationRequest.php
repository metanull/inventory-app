<?php

namespace App\Http\Requests\PictureTranslation;

use Illuminate\Foundation\Http\FormRequest;

class IndexPictureTranslationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'filter' => ['sometimes', 'array'],
            'filter.picture_id' => ['sometimes', 'uuid', 'exists:pictures,id'],
            'filter.language_id' => ['sometimes', 'string', 'size:3', 'exists:languages,id'],
            'filter.context_id' => ['sometimes', 'uuid', 'exists:contexts,id'],
            'sort' => ['sometimes', 'string', 'in:description,caption,created_at,updated_at'],
            'order' => ['sometimes', 'string', 'in:asc,desc'],
        ];
    }
}
