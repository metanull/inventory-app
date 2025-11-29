<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCountryTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $countryTranslation = $this->route('countryTranslation');

        $uniqueRule = Rule::unique('country_translations')
            ->where('country_id', $this->input('country_id'))
            ->where('language_id', $this->input('language_id'))
            ->ignore($countryTranslation->id);

        return [
            'id' => ['prohibited'],
            'country_id' => ['sometimes', 'string', 'size:3', 'exists:countries,id', $uniqueRule],
            'language_id' => ['sometimes', 'string', 'size:3', 'exists:languages,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'backward_compatibility' => ['nullable', 'string', 'max:255'],
            'extra' => ['nullable', 'array'],
        ];
    }
}
