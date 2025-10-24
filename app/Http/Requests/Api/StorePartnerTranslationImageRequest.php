<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StorePartnerTranslationImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'id' => ['prohibited'],
            'partner_translation_id' => ['required', 'uuid', 'exists:partner_translations,id'],
            'path' => ['required', 'string', 'max:255'],
            'original_name' => ['required', 'string', 'max:255'],
            'mime_type' => ['required', 'string', 'max:100'],
            'size' => ['required', 'integer', 'min:0'],
            'alt_text' => ['nullable', 'string'],
            'display_order' => ['required', 'integer', 'min:1'],
        ];
    }
}
