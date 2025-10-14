<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreThemeTranslationRequest extends FormRequest
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
        return [
            'theme_id' => [
                'required',
                'uuid',
                'exists:themes,id',
                Rule::unique('theme_translations')->where(function ($query) {
                    return $query->where('language_id', $this->input('language_id'))
                                 ->where('context_id', $this->input('context_id'));
                }),
            ],
            'language_id' => ['required', 'string', 'exists:languages,id'],
            'context_id' => ['required', 'uuid', 'exists:contexts,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'introduction' => ['required', 'string'],
            'backward_compatibility' => ['nullable', 'string', 'max:255'],
            'extra' => ['nullable', 'json'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'theme_id.unique' => 'A translation for this theme, language and context combination already exists.',
        ];
    }
}
