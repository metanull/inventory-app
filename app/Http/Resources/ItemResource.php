<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'internal_name' => $this->internal_name,
            'backward_compatibility' => $this->backward_compatibility,
            'type' => $this->type,
            'partner' => new PartnerResource($this->whenLoaded('partner')),
            'project' => new ProjectResource($this->whenLoaded('project')),
            'country' => new CountryResource($this->whenLoaded('country')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
