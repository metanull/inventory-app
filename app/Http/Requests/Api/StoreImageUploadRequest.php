<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Get image upload rules from configuration
        $imageUploadRules = [
            'mime' => config('localstorage.uploads.images.mime', 'jpeg,png,jpg'),
            'max_size' => config('localstorage.uploads.images.max_size', 20480),
        ];

        return [
            'file' => "required|image|mimes:{$imageUploadRules['mime']}|max:{$imageUploadRules['max_size']}",
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
