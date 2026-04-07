<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDynastyRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => ['prohibited'],
            'from_ah' => ['nullable', 'integer'],
            'to_ah' => ['nullable', 'integer'],
            'from_ad' => ['nullable', 'integer'],
            'to_ad' => ['nullable', 'integer'],
            'backward_compatibility' => ['nullable', 'string'],
        ];
    }
}
