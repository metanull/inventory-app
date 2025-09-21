<?php

namespace App\Http\Requests\ItemTranslation;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemTranslationRequest extends FormRequest
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
            'item_id' => ['required', 'uuid', 'exists:items,id'],
            'language_id' => ['required', 'string', 'size:3', 'exists:languages,id'],
            'context_id' => ['required', 'uuid', 'exists:contexts,id'],
            'name' => ['required', 'string', 'max:255'],
            'alternate_name' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'type' => ['nullable', 'string', 'max:255'],
            'holder' => ['nullable', 'string', 'max:255'],
            'owner' => ['nullable', 'string', 'max:255'],
            'initial_owner' => ['nullable', 'string', 'max:255'],
            'dates' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'dimensions' => ['nullable', 'string', 'max:255'],
            'place_of_production' => ['nullable', 'string', 'max:255'],
            'method_for_datation' => ['nullable', 'string', 'max:255'],
            'method_for_provenance' => ['nullable', 'string', 'max:255'],
            'obtention' => ['nullable', 'string', 'max:255'],
            'bibliography' => ['nullable', 'string'],
            'author_id' => ['nullable', 'uuid', 'exists:authors,id'],
            'text_copy_editor_id' => ['nullable', 'uuid', 'exists:authors,id'],
            'translator_id' => ['nullable', 'uuid', 'exists:authors,id'],
            'translation_copy_editor_id' => ['nullable', 'uuid', 'exists:authors,id'],
            'backward_compatibility' => ['nullable', 'string', 'max:255'],
            'extra' => ['nullable', 'json'],
        ];
    }
}
