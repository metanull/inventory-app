<?php

namespace App\Http\Requests\ImageUpload;

use Illuminate\Foundation\Http\FormRequest;

class StoreImageUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Get dynamic rules from configuration
        $imageUploadRules = [
            'mime' => config('localstorage.uploads.images.mime', 'jpeg,png,jpg'),
            'max_size' => config('localstorage.uploads.images.max_size', 20480),
        ];

        return [
            'file' => "required|image|mimes:{$imageUploadRules['mime']}|max:{$imageUploadRules['max_size']}",
            // Explicitly prohibit internal fields
            'id' => 'prohibited',
            'path' => 'prohibited',
            'name' => 'prohibited',
            'extension' => 'prohibited',
            'mime_type' => 'prohibited',
            'size' => 'prohibited',
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
            'file.required' => 'A file is required for upload.',
            'file.image' => 'The uploaded file must be an image.',
            'file.mimes' => 'The image must be of type: '.config('localstorage.uploads.images.mime', 'jpeg,png,jpg'),
            'file.max' => 'The image size must not exceed '.config('localstorage.uploads.images.max_size', 20480).' KB.',
            'id.prohibited' => 'The ID field cannot be set manually.',
            'path.prohibited' => 'The path field cannot be set manually.',
            'name.prohibited' => 'The name field cannot be set manually.',
            'extension.prohibited' => 'The extension field cannot be set manually.',
            'mime_type.prohibited' => 'The mime_type field cannot be set manually.',
            'size.prohibited' => 'The size field cannot be set manually.',
        ];
    }
}
