<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Api\Concerns\HasIncludes;
use Illuminate\Foundation\Http\FormRequest;

class ShowGlossaryTranslationRequest extends FormRequest
{
    use HasIncludes;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'include' => 'sometimes|string',
        ];
    }

    protected function getIncludeAllowlistKey(): string
    {
        return 'glossary_translation';
    }
}
