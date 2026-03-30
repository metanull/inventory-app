<?php

namespace App\Http\Requests\Api;

use App\Rules\IncludeRule;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AttachFromAvailableContributorImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'available_image_id' => ['required', 'uuid', 'exists:available_images,id'],
            'alt_text' => ['nullable', 'string'],
            'include' => ['sometimes', 'string', new IncludeRule('contributor_image')],
        ];
    }

    public function getIncludeParams(): array
    {
        return IncludeParser::fromRequest($this, AllowList::for('contributor_image'));
    }
}
