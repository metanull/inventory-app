<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTagRequest extends FormRequest
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
        $tag = $this->route('tag');

        return [
            'id' => ['prohibited'],
            'internal_name' => ['required', 'string', Rule::unique('tags', 'internal_name')->ignore($tag?->id)],
            'backward_compatibility' => ['nullable', 'string'],
            'description' => ['required', 'string'],
        ];
    }
}
