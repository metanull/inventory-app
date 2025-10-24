<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePartnerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => ['prohibited'],
            'internal_name' => ['required', 'string'],
            'backward_compatibility' => ['nullable', 'string'],
            'type' => ['required', 'in:museum,institution,individual'],
            'country_id' => ['nullable', 'string', 'size:3', 'exists:countries,id'],
            // GPS Location
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'map_zoom' => ['nullable', 'integer', 'between:1,20'],
            // Relationships
            'project_id' => ['nullable', 'uuid', 'exists:projects,id'],
            'monument_item_id' => ['nullable', 'uuid', 'exists:items,id'],
            // Visibility
            'visible' => ['sometimes', 'boolean'],
        ];
    }
}
