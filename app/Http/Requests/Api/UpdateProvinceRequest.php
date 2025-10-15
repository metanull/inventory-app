<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProvinceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $province = $this->route('province');

        return [
            'internal_name' => ['required', 'string', Rule::unique('provinces', 'internal_name')->ignore($province?->id)],
            'country_id' => ['required', 'exists:countries,id'],
            'backward_compatibility' => ['nullable', 'string'],
            'translations' => ['array', 'min:1'],
            'translations.*.language_id' => ['required_with:translations', 'exists:languages,id'],
            'translations.*.name' => ['required_with:translations', 'string'],
        ];
    }
}
