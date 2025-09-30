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
        return [
            // The unique identifier (GUID)
            'id' => $this->id,
            // A name for this resource, for internal use only.
            'internal_name' => $this->internal_name,
            // The Id(s) of matching resource in the legacy system (if any).
            'backward_compatibility' => $this->backward_compatibility,
            // The type of the item: 'object', 'monument', 'detail', or 'picture'.
            'type' => $this->type,
            // The parent item ID (for hierarchical relationships), nullable
            'parent_id' => $this->parent_id,
            // Owner's reference number for the item (external reference from owner)
            'owner_reference' => $this->owner_reference,
            // MWNF reference number for the item (reference from MWNF system)
            'mwnf_reference' => $this->mwnf_reference,
            // The parent item (for hierarchical relationships), nullable (ItemResource)
            'parent' => new ItemResource($this->whenLoaded('parent')),
            // The child items (for hierarchical relationships) (ItemResource[])
            'children' => ItemResource::collection($this->whenLoaded('children')),
            // The partner owning the item (PartnerResource)
            'partner' => new PartnerResource($this->whenLoaded('partner')),
            // The project this item belongs to, nullable (ProjectResource)
            'project' => new ProjectResource($this->whenLoaded('project')),
            // The country this item is associated with, nullable (CountryResource)
            'country' => new CountryResource($this->whenLoaded('country')),
            // The collection that contains this item (CollectionResource)
            'collection' => new CollectionResource($this->whenLoaded('collection')),
            // Artists associated with this item (ArtistResource[])
            'artists' => ArtistResource::collection($this->whenLoaded('artists')),
            // Workshops associated with this item (WorkshopResource[])
            'workshops' => WorkshopResource::collection($this->whenLoaded('workshops')),
            // Tags associated with this item (TagResource[])
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            // Translations for this item (internationalization and contextualization) (ItemTranslationResource[])
            'translations' => ItemTranslationResource::collection($this->whenLoaded('translations')),
            // Item images attached to this item with display ordering (ItemImageResource[])
            'itemImages' => ItemImageResource::collection($this->whenLoaded('itemImages')),
            // The date of creation of the resource (managed by the system)
            'created_at' => $this->created_at,
            // The date of last modification of the resource (managed by the system)
            'updated_at' => $this->updated_at,
        ];
    }
}
