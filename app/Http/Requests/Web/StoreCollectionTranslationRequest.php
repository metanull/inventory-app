<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StoreCollectionTranslationRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],

            // Optional string fields
            'description' => ['nullable', 'string'],
            'url' => ['nullable', 'string', 'max:255'],

            // Legacy and extra
            'backward_compatibility' => ['nullable', 'string', 'max:255'],
            'extra' => ['nullable', 'json'],
        ];
    }

    /**
     * Add uniqueness validation for the combination of collection_id, language_id, and context_id.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $exists = \App\Models\CollectionTranslation::where('collection_id', $this->collection_id)
                ->where('language_id', $this->language_id)
                ->where('context_id', $this->context_id)
                ->exists();

            if ($exists) {
                $validator->errors()->add('collection_id', 'This combination of collection, language, and context already exists.');
            }
        });
    }
}
