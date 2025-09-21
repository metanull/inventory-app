<?php

namespace App\Http\Requests\ProvinceTranslation;

use Illuminate\Foundation\Http\FormRequest;

class ShowProvinceTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['include' => ['sometimes', 'string']];
    }
}
