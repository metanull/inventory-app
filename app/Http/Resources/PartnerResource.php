<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerResource extends JsonResource
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
            // The unique identifier of the partner (GUID)
            'id' => $this->id,
            // The name of the partner, it shall only be used internally
            'internal_name' => $this->internal_name,
            // The legacy Id when this partner corresponds to a legacy partner from the MWNF3 database, nullable
            'backward_compatibility' => $this->backward_compatibility,
            // The type of the partner, either 'museum',  'institution' or 'individual'
            'type' => $this->type,
            // The country this partner is associated with, nullable
            'country' => new CountryResource($this->whenLoaded('country')),
            // Date of creation
            'created_at' => $this->created_at,
            // Date of last modification
            'updated_at' => $this->updated_at,
        ];
    }
}
