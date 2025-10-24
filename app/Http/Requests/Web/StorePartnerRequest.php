<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StorePartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'internal_name' => ['required', 'string'],
            'type' => ['required', 'in:museum,institution,individual'],
            'backward_compatibility' => ['nullable', 'string'],
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
