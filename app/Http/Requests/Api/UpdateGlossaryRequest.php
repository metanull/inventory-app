<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGlossaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    
    public function rules(): array
    {
        /** @var \App\Models\Glossary $glossary */
        $glossary = $this->route('glossary');

        return [
            'id' => ['prohibited'],
            'internal_name' => ['required', 'string', 'unique:glossaries,internal_name,'.$glossary->id],
            'backward_compatibility' => ['nullable', 'string'],
        ];
    }
}
