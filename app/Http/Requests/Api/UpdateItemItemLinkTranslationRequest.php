<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemItemLinkTranslationRequest extends FormRequest
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
        $translation = $this->route('itemItemLinkTranslation');
        $translationId = $translation instanceof \App\Models\ItemItemLinkTranslation ? $translation->id : $translation;

        return [
            'id' => ['prohibited'],
            'item_item_link_id' => [
                'required',
                'uuid',
                'exists:item_item_links,id',
            ],
            'language_id' => [
                'required',
                'string',
                'size:3',
                'exists:languages,id',
                Rule::unique('item_item_link_translations')
                    ->where('item_item_link_id', $this->input('item_item_link_id'))
                    ->ignore($translationId),
            ],
            'description' => ['nullable', 'string'],
            'reciprocal_description' => ['nullable', 'string'],
            'backward_compatibility' => ['nullable', 'string', 'max:255'],
            'created_at' => ['prohibited'],
            'updated_at' => ['prohibited'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'language_id.unique' => 'A translation already exists for this link in this language.',
        ];
    }
}
