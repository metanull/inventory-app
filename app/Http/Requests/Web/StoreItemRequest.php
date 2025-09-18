<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null; // Additional policy checks can be added later
    }

    public function rules(): array
    {
        return [
            'internal_name' => ['required', 'string', 'max:255'],
            'backward_compatibility' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:object,monument'],
            'country_id' => ['nullable', 'string', 'size:3', 'exists:countries,id'],
        ];
    }
}
