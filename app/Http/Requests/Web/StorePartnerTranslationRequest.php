<?php

namespace App\Http\Requests\Web;

use App\Http\Requests\Traits\PreparesPairsForValidation;
use Illuminate\Foundation\Http\FormRequest;

class StorePartnerTranslationRequest extends FormRequest
{
    use PreparesPairsForValidation;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function prepareForValidation(): void
    {
        $this->preparePairsField('extra');
    }

    public function rules(): array
    {
        return [
            // Required fields
            'partner_id' => ['required', 'uuid', 'exists:partners,id'],
            'language_id' => ['required', 'string', 'max:10', 'exists:languages,id'],
            'context_id' => ['required', 'uuid', 'exists:contexts,id'],
            'name' => ['required', 'string', 'max:255'],

            // Optional string fields
            'description' => ['nullable', 'string'],
            'city_display' => ['nullable', 'string', 'max:255'],

            // Address fields
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'address_notes' => ['nullable', 'string'],

            // Contact fields
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_email_general' => ['nullable', 'email', 'max:255'],
            'contact_email_press' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_website' => ['nullable', 'url', 'max:255'],
            'contact_notes' => ['nullable', 'string'],
            'contact_emails' => ['nullable', 'array'],
            'contact_emails.*' => ['email', 'max:255'],
            'contact_phones' => ['nullable', 'array'],
            'contact_phones.*' => ['string', 'max:50'],

            // Legacy and extra
            'backward_compatibility' => ['nullable', 'string', 'max:255'],
            'extra' => ['nullable', 'json'],
        ];
    }

    /**
     * Add uniqueness validation for the combination of partner_id, language_id, and context_id.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $exists = \App\Models\PartnerTranslation::where('partner_id', $this->partner_id)
                ->where('language_id', $this->language_id)
                ->where('context_id', $this->context_id)
                ->exists();

            if ($exists) {
                $validator->errors()->add('partner_id', 'This combination of partner, language, and context already exists.');
            }
        });
    }
}
