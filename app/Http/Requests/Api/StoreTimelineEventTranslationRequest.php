<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTimelineEventTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => ['prohibited'],
            'timeline_event_id' => ['required', 'string', 'exists:timeline_events,id'],
            'language_id' => ['required', 'string', 'size:3', 'exists:languages,id'],
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'date_from_description' => ['nullable', 'string', 'max:255'],
            'date_to_description' => ['nullable', 'string', 'max:255'],
            'date_from_ah_description' => ['nullable', 'string', 'max:255'],
            'backward_compatibility' => ['nullable', 'string'],
            'extra' => ['nullable', 'json'],
        ];
    }
}
