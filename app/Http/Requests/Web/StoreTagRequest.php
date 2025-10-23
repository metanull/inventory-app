<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StoreTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'internal_name' => ['required', 'string', 'max:255', 'unique:tags,internal_name'],
            'description' => ['required', 'string', 'max:1000'],
            'backward_compatibility' => ['nullable', 'string', 'max:255'],
        ];
    }
}
