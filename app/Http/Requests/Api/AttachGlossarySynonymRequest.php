<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AttachGlossarySynonymRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'synonym_id' => ['required', 'uuid', 'exists:glossaries,id'],
        ];
    }
}
