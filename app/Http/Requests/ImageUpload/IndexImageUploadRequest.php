<?php

namespace App\Http\Requests\ImageUpload;

use Illuminate\Foundation\Http\FormRequest;

class IndexImageUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // ImageUpload index doesn't support pagination or includes currently
        return [];
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
