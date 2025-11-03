<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemTranslationRequest extends FormRequest
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
            'item_id' => ['required', 'uuid', 'exists:items,id'],
            'language_id' => ['required', 'string', 'max:10', 'exists:languages,id'],
            'context_id' => ['required', 'uuid', 'exists:contexts,id'],
            'name' => ['required', 'string', 'max:255'],

            // Optional string fields
            'description' => ['nullable', 'string'],
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
     * Add uniqueness validation for the combination of item_id, language_id, and context_id.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $exists = \App\Models\ItemTranslation::where('item_id', $this->item_id)
                ->where('language_id', $this->language_id)
                ->where('context_id', $this->context_id)
                ->exists();

            if ($exists) {
                $validator->errors()->add('item_id', 'This combination of item, language, and context already exists.');
            }
        });
    }
}
