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
            'id' => $this->id,
            'contact_id' => $this->contact_id,
            'language_id' => $this->language_id,
            'label' => $this->label,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
