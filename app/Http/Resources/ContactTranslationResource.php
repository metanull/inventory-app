<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ContactTranslationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            // The unique identifier (GUID)
            'id' => $this->id,
            // The contact this translation belongs to (ContactResource id)
            'contact_id' => $this->contact_id,
            // The language of this translation (LanguageResource id)
            'language_id' => $this->language_id,
            // The label for the contact translation
            'label' => $this->label,
            // The date of creation of the resource (managed by the system)
            'created_at' => $this->created_at,
            // The date of last modification of the resource (managed by the system)
            'updated_at' => $this->updated_at,
        ];
    }
}
