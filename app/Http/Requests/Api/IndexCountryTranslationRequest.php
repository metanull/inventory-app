<?php

namespace App\Http\Requests\Api;

use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use App\Support\Pagination\PaginationParams;
use Illuminate\Foundation\Http\FormRequest;

class IndexCountryTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'include' => 'sometimes|string',
        ];
    }

    public function getPaginationParams(): array
    {
        return PaginationParams::fromRequest($this);
    }

    public function getIncludeParams(): array
    {
        return IncludeParser::fromRequest($this, AllowList::for('country_translation'));
    }
}
