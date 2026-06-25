<?php

namespace App\Http\Requests\Api;

use App\Models\Glossary;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGlossaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Glossary $glossary */
        $glossary = $this->route('glossary');

        return [
            'id' => ['prohibited'],
            'internal_name' => ['required', 'string', 'unique:glossaries,internal_name,'.$glossary->id],
            'backward_compatibility' => ['nullable', 'string'],
        ];
    }
}
