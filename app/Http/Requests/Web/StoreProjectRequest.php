<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'internal_name' => ['required', 'string', 'max:255'],
            'backward_compatibility' => ['nullable', 'string', 'max:255'],
            'launch_date' => ['nullable', 'date'],
            'is_launched' => ['boolean'],
            'is_enabled' => ['boolean'],
            'context_id' => ['nullable', 'uuid', 'exists:contexts,id'],
            'language_id' => ['nullable', 'string', 'size:3', 'exists:languages,id'],
        ];
    }
}
