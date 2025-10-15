<?php

namespace App\Http\Requests\Api;

use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use Illuminate\Foundation\Http\FormRequest;

class ShowItemTranslationRequest extends FormRequest
{
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

    /**
     * Get validated include parameters.
     *
     * @return array<int, string>
     */
    public function getIncludeParams(): array
    {
        return IncludeParser::fromRequest($this, AllowList::for('item_translation'));
    }
}
