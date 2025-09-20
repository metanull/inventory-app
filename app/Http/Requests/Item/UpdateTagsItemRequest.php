<?php

namespace App\Http\Requests\Item;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTagsItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'attach' => 'sometimes|array',
            'attach.*' => 'required|uuid|exists:tags,id',
            'detach' => 'sometimes|array',
            'detach.*' => 'required|uuid|exists:tags,id',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $allowedKeys = array_keys($this->rules());
            $inputKeys = array_keys($this->all());
            $unexpectedKeys = array_diff($inputKeys, $allowedKeys);

            if (! empty($unexpectedKeys)) {
                foreach ($unexpectedKeys as $key) {
                    $validator->errors()->add($key, "The {$key} field is not allowed.");
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'attach.array' => 'The attach parameter must be an array.',
            'attach.*.required' => 'Each tag ID in attach is required.',
            'attach.*.uuid' => 'Each tag ID in attach must be a valid UUID.',
            'attach.*.exists' => 'Each tag ID in attach must exist in the database.',
            'detach.array' => 'The detach parameter must be an array.',
            'detach.*.required' => 'Each tag ID in detach is required.',
            'detach.*.uuid' => 'Each tag ID in detach must be a valid UUID.',
            'detach.*.exists' => 'Each tag ID in detach must exist in the database.',
        ];
    }
}
