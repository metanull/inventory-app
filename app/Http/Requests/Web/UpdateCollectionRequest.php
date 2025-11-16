<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCollectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'internal_name' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', 'in:collection,exhibition,gallery,theme,exhibition trail,itinerary,location'],
            'language_id' => ['sometimes', 'required', 'string', 'size:3', 'exists:languages,id'],
            'context_id' => ['sometimes', 'required', 'uuid', 'exists:contexts,id'],
            'parent_id' => ['nullable', 'uuid', 'exists:collections,id'],
            'backward_compatibility' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
