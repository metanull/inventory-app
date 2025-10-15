<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
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
            'id' => ['prohibited'],
            'internal_name' => ['required', 'string'],
            'backward_compatibility' => ['nullable', 'string'],
            'launch_date' => ['nullable', 'date'],
            'is_launched' => ['boolean'],
            'is_enabled' => ['boolean'],
            'context_id' => ['nullable', 'uuid'],
            'language_id' => ['nullable', 'string', 'size:3'],
        ];
    }
}
