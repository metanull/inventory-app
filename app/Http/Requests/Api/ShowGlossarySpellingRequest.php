<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Api\Concerns\HasIncludes;
use App\Rules\IncludeRule;
use Illuminate\Foundation\Http\FormRequest;

class ShowGlossarySpellingRequest extends FormRequest
{
    use HasIncludes;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'include' => ['sometimes', 'string', new IncludeRule('glossary_spelling')],
        ];
    }

    protected function getIncludeAllowlistKey(): string
    {
        return 'glossary_spelling';
    }
}
