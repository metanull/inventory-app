<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCollectionTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            // Required fields
            'collection_id' => ['required', 'uuid', 'exists:collections,id'],
            'language_id' => ['required', 'string', 'max:10', 'exists:languages,id'],
            'context_id' => ['required', 'uuid', 'exists:contexts,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],

            // Optional string fields
            'alternate_name' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'holder' => ['nullable', 'string'],
            'owner' => ['nullable', 'string'],
            'initial_owner' => ['nullable', 'string'],
            'dates' => ['nullable', 'string'],
            'location' => ['nullable', 'string'],
            'dimensions' => ['nullable', 'string'],
            'place_of_production' => ['nullable', 'string'],
            'method_for_datation' => ['nullable', 'string'],
            'method_for_provenance' => ['nullable', 'string'],
            'obtention' => ['nullable', 'string'],
            'bibliography' => ['nullable', 'string'],

            // Author references
            'author_id' => ['nullable', 'uuid', 'exists:authors,id'],
            'text_copy_editor_id' => ['nullable', 'uuid', 'exists:authors,id'],
            'translator_id' => ['nullable', 'uuid', 'exists:authors,id'],
            'translation_copy_editor_id' => ['nullable', 'uuid', 'exists:authors,id'],

            // Legacy and extra
            'backward_compatibility' => ['nullable', 'string', 'max:255'],
            'extra' => ['nullable', 'json'],
        ];
    }

    /**
     * Add uniqueness validation for the combination of collection_id, language_id, and context_id.
     * Exclude the current translation from the uniqueness check.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $collectionTranslation = $this->route('collection_translation');

            if (! $collectionTranslation) {
                return;
            }

            $exists = \App\Models\CollectionTranslation::where('collection_id', $this->collection_id)
                ->where('language_id', $this->language_id)
                ->where('context_id', $this->context_id)
                ->where('id', '!=', $collectionTranslation->id)
                ->exists();

            if ($exists) {
                $validator->errors()->add('collection_id', 'This combination of collection, language, and context already exists.');
            }
        });
    }
}
