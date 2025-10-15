<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreThemeRequest extends FormRequest
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
            'exhibition_id' => ['required', 'uuid', 'exists:exhibitions,id'],
            'parent_id' => ['nullable', 'uuid', 'exists:themes,id'],
            'internal_name' => ['required', 'string'],
            'backward_compatibility' => ['nullable', 'string'],
        ];
    }
}
