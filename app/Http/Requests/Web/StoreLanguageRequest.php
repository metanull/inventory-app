<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StoreLanguageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'string', 'size:3', 'unique:languages,id'],
            'internal_name' => ['required', 'string', 'max:255'],
            'backward_compatibility' => ['nullable', 'string', 'max:2'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }
}
