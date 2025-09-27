<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class IndexExhibitionTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'exhibition_id' => 'sometimes|uuid|exists:exhibitions,id',
            'language_id' => 'sometimes|string|size:3|exists:languages,id',
            'context_id' => 'sometimes|uuid|exists:contexts,id',
            'default_context' => 'sometimes|boolean',
        ];
    }
}
