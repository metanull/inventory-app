<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTagsItemRequest extends FormRequest
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
            'attach' => ['sometimes', 'array'],
            'attach.*' => ['required', 'uuid', 'exists:tags,id'],
            'detach' => ['sometimes', 'array'],
            'detach.*' => ['required', 'uuid', 'exists:tags,id'],
        ];
    }
}
