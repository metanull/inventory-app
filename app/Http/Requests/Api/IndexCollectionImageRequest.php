<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class IndexCollectionImageRequest extends FormRequest
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
            'include' => ['sometimes', 'string'],
        ];
    }

    /**
     * Get validated include parameters.
     *
     * @return array<int, string>
     */
    public function getIncludeParams(): array
    {
        return \App\Support\Includes\IncludeParser::fromRequest($this, \App\Support\Includes\AllowList::for('collectionImage'));
    }
}
