<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class VerifyTwoFactorMobileAppAuthenticationRequest extends FormRequest
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
        return [
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'required|string|max:255',
            'code' => 'required|string',
            'method' => 'sometimes|string|in:totp,email',
            'wipe_tokens' => 'sometimes|boolean',
        ];
    }
}
