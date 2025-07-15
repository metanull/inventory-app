<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProvinceTranslationResource extends JsonResource
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
            // The province this translation belongs to (ProvinceResource id)
            'province_id' => $this->province_id,
            // The language of this translation (LanguageResource id)
            'language_id' => $this->language_id,
            // The name of the province translation
            'name' => $this->name,
            // The description of the province translation
            'description' => $this->description,
            // The date of creation of the resource (managed by the system)
            'created_at' => $this->created_at,
            // The date of last modification of the resource (managed by the system)
            'updated_at' => $this->updated_at,
        ];
    }
}
