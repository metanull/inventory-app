<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AddressTranslationResource extends JsonResource
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
            // The address this translation belongs to (AddressResource id)
            'address_id' => $this->address_id,
            // The language of this translation (LanguageResource id)
            'language_id' => $this->language_id,
            // The address translation text
            'address' => $this->address,
            // The description of the address translation
            'description' => $this->description,
            // The date of creation of the resource (managed by the system)
            'created_at' => $this->created_at,
            // The date of last modification of the resource (managed by the system)
            'updated_at' => $this->updated_at,
        ];
    }
}
