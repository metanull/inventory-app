<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTimelineRequest extends FormRequest
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
            'id' => ['prohibited'],
            'internal_name' => ['sometimes', 'string', 'max:255'],
            'country_id' => ['sometimes', 'string', 'size:3', 'exists:countries,id'],
            'collection_id' => ['nullable', 'string', 'exists:collections,id'],
            'backward_compatibility' => ['nullable', 'string'],
            'extra' => ['nullable', 'json'],
        ];
    }
}
