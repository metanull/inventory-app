<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCollectionTranslationRequest extends FormRequest
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
        $collectionTranslation = $this->route('collectionTranslation');

        // Only apply uniqueness validation if updating the unique key fields
        $uniqueRule = Rule::unique('collection_translations')->ignore($collectionTranslation?->id);

        // Build where clauses for unique constraint check
        // Use existing values if not being updated
        if ($this->has('language_id') || $this->has('context_id')) {
            $uniqueRule->where(function ($query) use ($collectionTranslation) {
                $query->where('language_id', $this->input('language_id', $collectionTranslation?->language_id))
                    ->where('context_id', $this->input('context_id', $collectionTranslation?->context_id));
            });
        }

        return [
            'id' => ['prohibited'],
            'collection_id' => [
                'sometimes',
                'uuid',
                'exists:collections,id',
                $uniqueRule,
            ],
            'language_id' => ['sometimes', 'string', 'size:3', 'exists:languages,id'],
            'context_id' => ['sometimes', 'uuid', 'exists:contexts,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'url' => ['nullable', 'url', 'max:2048'],
            'backward_compatibility' => ['nullable', 'string', 'max:255'],
            'extra' => ['nullable', 'json'],
        ];
    }
}
