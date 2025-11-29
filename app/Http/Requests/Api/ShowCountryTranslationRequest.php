<?php

namespace App\Http\Requests\Api;

use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use Illuminate\Foundation\Http\FormRequest;

class ShowCountryTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'include' => ['sometimes', 'string'],
        ];
    }

    public function getIncludeParams(): array
    {
        return IncludeParser::fromRequest($this, AllowList::for('country_translation'));
    }
}
