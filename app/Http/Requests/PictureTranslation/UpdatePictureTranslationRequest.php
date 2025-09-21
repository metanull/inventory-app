<?php

namespace App\Http\Requests\PictureTranslation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePictureTranslationRequest extends FormRequest
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
        $pictureTranslation = $this->route('picture_translation');

        return [
            'picture_id' => [
                'sometimes',
                'uuid',
                'exists:pictures,id',
                Rule::unique('picture_translations', 'picture_id')
                    ->where('language_id', $this->input('language_id', $pictureTranslation->language_id))
                    ->where('context_id', $this->input('context_id', $pictureTranslation->context_id))
                    ->ignore($pictureTranslation->id),
            ],
            'language_id' => [
                'sometimes',
                'string',
                'size:3',
                'exists:languages,id',
                Rule::unique('picture_translations', 'language_id')
                    ->where('picture_id', $this->input('picture_id', $pictureTranslation->picture_id))
                    ->where('context_id', $this->input('context_id', $pictureTranslation->context_id))
                    ->ignore($pictureTranslation->id),
            ],
            'context_id' => [
                'sometimes',
                'uuid',
                'exists:contexts,id',
                Rule::unique('picture_translations', 'context_id')
                    ->where('picture_id', $this->input('picture_id', $pictureTranslation->picture_id))
                    ->where('language_id', $this->input('language_id', $pictureTranslation->language_id))
                    ->ignore($pictureTranslation->id),
            ],
            'description' => ['sometimes', 'string'],
            'caption' => ['sometimes', 'string', 'max:255'],
            'author_id' => ['nullable', 'uuid', 'exists:authors,id'],
            'text_copy_editor_id' => ['nullable', 'uuid', 'exists:authors,id'],
            'translator_id' => ['nullable', 'uuid', 'exists:authors,id'],
            'translation_copy_editor_id' => ['nullable', 'uuid', 'exists:authors,id'],
            'backward_compatibility' => ['nullable', 'string', 'max:255'],
            'extra' => ['nullable', 'json'],
        ];
    }
}
