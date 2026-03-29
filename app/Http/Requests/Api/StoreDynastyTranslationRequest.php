<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDynastyTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $uniqueRule = Rule::unique('dynasty_translations')
            ->where('dynasty_id', $this->input('dynasty_id'))
            ->where('language_id', $this->input('language_id'));

        return [
            'id' => ['prohibited'],
            'dynasty_id' => ['required', 'uuid', 'exists:dynasties,id', $uniqueRule],
            'language_id' => ['required', 'string', 'size:3', 'exists:languages,id'],
            'name' => ['nullable', 'string', 'max:255'],
            'also_known_as' => ['nullable', 'string', 'max:255'],
            'area' => ['nullable', 'string'],
            'history' => ['nullable', 'string'],
            'date_description_ah' => ['nullable', 'string', 'max:255'],
            'date_description_ad' => ['nullable', 'string', 'max:255'],
            'backward_compatibility' => ['nullable', 'string', 'max:255'],
            'extra' => ['nullable', 'array'],
        ];
    }
}
