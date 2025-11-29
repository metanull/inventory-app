<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'internal_name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'in:keyword,material,artist,dynasty'],
            'language_id' => ['nullable', 'string', 'size:3', 'exists:languages,id'],
            'description' => ['required', 'string', 'max:1000'],
            'backward_compatibility' => ['nullable', 'string', 'max:255'],
        ];
    }
}
