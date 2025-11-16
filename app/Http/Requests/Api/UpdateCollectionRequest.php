<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCollectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $collection = $this->route('collection');

        return [
            'internal_name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('collections', 'internal_name')->ignore($collection?->id)],
            'type' => ['sometimes', 'required', 'in:collection,exhibition,gallery,theme,exhibition trail,itinerary,location'],
            'language_id' => ['sometimes', 'required', 'string', 'size:3', 'exists:languages,id'],
            'context_id' => ['sometimes', 'required', 'string', 'exists:contexts,id'],
            'parent_id' => ['nullable', 'string', 'exists:collections,id'],
            'backward_compatibility' => ['nullable', 'string', 'max:255'],
        ];
    }
}
