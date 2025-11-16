<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StoreCollectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'internal_name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:collection,exhibition,gallery,theme,exhibition trail,itinerary,location'],
            'language_id' => ['required', 'string', 'size:3', 'exists:languages,id'],
            'context_id' => ['required', 'uuid', 'exists:contexts,id'],
            'parent_id' => ['nullable', 'uuid', 'exists:collections,id'],
            'backward_compatibility' => ['nullable', 'string', 'max:255'],
        ];
    }
}
