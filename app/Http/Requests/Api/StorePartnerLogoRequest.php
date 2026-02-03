<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StorePartnerLogoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'id' => ['prohibited'],
            'partner_id' => ['required', 'uuid', 'exists:partners,id'],
            'path' => ['required', 'string', 'max:255'],
            'original_name' => ['required', 'string', 'max:255'],
            'mime_type' => ['required', 'string', 'max:100'],
            'size' => ['required', 'integer', 'min:0'],
            'logo_type' => ['sometimes', 'string', 'max:50'],
            'alt_text' => ['nullable', 'string'],
            'display_order' => ['required', 'integer', 'min:1'],
        ];
    }
}
