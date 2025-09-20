<?php

namespace App\Http\Requests\Address;

use Illuminate\Foundation\Http\FormRequest;

class ShowAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'include' => 'string',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $allowedKeys = array_keys($this->rules());
            $inputKeys = array_keys($this->query->all());
            $unexpectedKeys = array_diff($inputKeys, $allowedKeys);

            if (! empty($unexpectedKeys)) {
                foreach ($unexpectedKeys as $key) {
                    $validator->errors()->add($key, "The {$key} query parameter is not allowed.");
                }
            }
        });
    }
}
