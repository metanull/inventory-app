<?php

namespace App\Http\Requests\Api;

use App\Rules\IncludeRule;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use Illuminate\Foundation\Http\FormRequest;

class ShowLanguageTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'include' => ['sometimes', 'string', new IncludeRule('language_translation')],
        ];
    }

    public function getIncludeParams(): array
    {
        return IncludeParser::fromRequest($this, AllowList::for('language_translation'));
    }
}
