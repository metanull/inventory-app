<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateThemeRequest extends FormRequest
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
        $theme = $this->route('theme');

        return [
            'internal_name' => ['sometimes', 'string', Rule::unique('themes', 'internal_name')->ignore($theme?->id, 'id')],
            'backward_compatibility' => ['nullable', 'string'],
        ];
    }
}
