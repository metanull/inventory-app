<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Contact resource for API responses.
 */
class ContactResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // The unique identifier (GUID)
            'id' => $this->id,
            // A name for this resource, for internal use only.
            'internal_name' => $this->internal_name,
            // The phone number of the contact
            'phone_number' => $this->phone_number,
            // The formatted phone number of the contact
            'formatted_phone_number' => $this->formattedPhoneNumber(),
            // The fax number of the contact
            'fax_number' => $this->fax_number,
            // The formatted fax number of the contact
            'formatted_fax_number' => $this->formattedFaxNumber(),
            // The email address of the contact
            'email' => $this->email,
            // Translations for this contact (ContactTranslationResource[])
            'translations' => $this->whenLoaded('translations', function () {
                return ContactTranslationResource::collection($this->translations);
            }),
            // The date of creation of the resource (managed by the system)
            'created_at' => $this->created_at,
            // The date of last modification of the resource (managed by the system)
            'updated_at' => $this->updated_at,
        ];
    }
}
