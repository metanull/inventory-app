<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StorePartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'internal_name' => ['required', 'string'],
            'type' => ['required', 'in:museum,institution,individual'],
            'backward_compatibility' => ['nullable', 'string'],
            'country_id' => ['nullable', 'string', 'size:3', 'exists:countries,id'],
        ];
    }
}
