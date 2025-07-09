<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExhibitionResource extends JsonResource
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
            'internal_name' => $this->internal_name,
            'backward_compatibility' => $this->backward_compatibility,
            'translations' => ExhibitionTranslationResource::collection($this->whenLoaded('translations')),
            'partners' => PartnerResource::collection($this->whenLoaded('partners')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
