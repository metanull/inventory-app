<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCollectionTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function prepareForValidation(): void
    {
        // Handle Livewire key-value editor component data
        if ($this->has('pairs')) {
            $extra = [];
            foreach ($this->input('pairs', []) as $pair) {
                if (! empty($pair['key'])) {
                    $extra[$pair['key']] = $pair['value'];
                }
            }
            $this->merge(['extra' => empty($extra) ? null : json_encode($extra)]);
        }

        // Keep existing array-to-JSON conversion for backward compatibility
        if ($this->has('extra') && is_array($this->extra)) {
            $this->merge([
                'extra' => empty($this->extra) ? null : json_encode($this->extra),
            ]);
        }
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
