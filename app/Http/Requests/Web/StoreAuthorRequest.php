<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StoreAuthorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'internal_name' => ['nullable', 'string', 'max:255'],
            'backward_compatibility' => ['nullable', 'string', 'max:255'],
        ];
    }
}
