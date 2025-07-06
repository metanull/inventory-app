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
            // The unique identifier of the item (GUID)
            'id' => $this->id,
            // The name of the item, it shall only be used internally
            'internal_name' => $this->internal_name,
            // The legacy Id when this item corresponds to a legacy item from the MWNF3 database, nullable
            'backward_compatibility' => $this->backward_compatibility,
            // The type of the item, either 'object' or 'monument'
            'type' => $this->type,
            // Owner's reference number for the item
            'owner_reference' => $this->owner_reference,
            // MWNF reference number for the item
            'mwnf_reference' => $this->mwnf_reference,
            // The partner owning the item
            'partner' => new PartnerResource($this->whenLoaded('partner')),
            // The project this item belongs to, nullable
            'project' => new ProjectResource($this->whenLoaded('project')),
            // The country this item is associated with, nullable
            'country' => new CountryResource($this->whenLoaded('country')),
            // Artists associated with this item
            'artists' => ArtistResource::collection($this->artists),
            // Workshops associated with this item
            'workshops' => WorkshopResource::collection($this->workshops),
            // Translations for this item (internationalization and contextualization)
            'translations' => ItemTranslationResource::collection($this->whenLoaded('translations')),
            // Date of creation
            'created_at' => $this->created_at,
            // Date of last modification
            'updated_at' => $this->updated_at,
        ];
    }
}
