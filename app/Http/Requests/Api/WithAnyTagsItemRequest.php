<?php

namespace App\Http\Requests\Api;

use App\Rules\IncludeRule;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use Illuminate\Foundation\Http\FormRequest;

class WithAnyTagsItemRequest extends FormRequest
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
            'tags' => ['required', 'array', 'min:1'],
            'tags.*' => ['required', 'uuid', 'exists:tags,id'],
            'include' => ['sometimes', 'string', new IncludeRule('item')],
        ];
    }

    /**
     * Get validated include parameters.
     *
     * @return array<int, string>
     */
    public function getIncludeParams(): array
    {
        return IncludeParser::fromRequest($this, AllowList::for('item'));
    }
}
