<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StoreImageUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        // Get image upload rules from configuration
        $imageUploadRules = [
            'mime' => config('localstorage.uploads.images.mime', 'jpeg,png,jpg'),
            'max_size' => config('localstorage.uploads.images.max_size', 20480),
        ];

        return [
            'file' => [
                'required',
                'image',
                "mimes:{$imageUploadRules['mime']}",
                "max:{$imageUploadRules['max_size']}",
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please select an image file to upload.',
            'file.image' => 'The file must be an image.',
            'file.mimes' => 'The image must be a file of type: :values.',
            'file.max' => 'The image must not be larger than :max kilobytes.',
        ];
    }
}
