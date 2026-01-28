<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Api\Concerns\HasPaginationAndIncludes;
use App\Rules\IncludeRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexGlossaryTranslationRequest extends FormRequest
{
    use HasPaginationAndIncludes;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'include' => ['sometimes', 'string', new IncludeRule('glossary_translation')],
        ];
    }

    protected function getIncludeAllowlistKey(): string
    {
        return 'glossary_translation';
    }
}
