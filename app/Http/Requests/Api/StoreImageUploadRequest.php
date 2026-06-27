<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;

class StoreImageUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $mime = Config::string('localstorage.uploads.images.mime', 'jpeg,png,jpg');
        $maxSize = Config::integer('localstorage.uploads.images.max_size', 20480);

        return [
            'file' => "required|image|mimes:{$mime}|max:{$maxSize}",
            /** @ignoreParam */
            'id' => 'prohibited',
            /** @ignoreParam */
            'path' => 'prohibited',
            /** @ignoreParam */
            'name' => 'prohibited',
            /** @ignoreParam */
            'extension' => 'prohibited',
            /** @ignoreParam */
            'mime_type' => 'prohibited',
            /** @ignoreParam */
            'size' => 'prohibited',
        ];
    }
}
