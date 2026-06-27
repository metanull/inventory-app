<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;

class StoreImageUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $mime = Config::string('localstorage.uploads.images.mime', 'jpeg,png,jpg');
        $maxSize = Config::integer('localstorage.uploads.images.max_size', 20480);

        return [
            'file' => [
                'required',
                'image',
                "mimes:{$mime}",
                "max:{$maxSize}",
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
