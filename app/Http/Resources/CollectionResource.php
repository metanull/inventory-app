<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Collection Resource
 *
 * Transforms Collection model data for API responses.
 * Includes relationships and computed attributes for API consumption.
 */
class CollectionResource extends JsonResource
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
            // The type of collection: 'collection', 'exhibition', or 'gallery'
            'type' => $this->type,
            // The language this collection belongs to (LanguageResource id)
            'language_id' => $this->language_id,
            // The context this collection belongs to (ContextResource id)
            'context_id' => $this->context_id,
            // The parent collection ID (for hierarchical organization)
            'parent_id' => $this->parent_id,
            // The Id(s) of matching resource in the legacy system (if any).
            'backward_compatibility' => $this->backward_compatibility,
            // The date of creation of the resource (managed by the system)
            'created_at' => $this->created_at,
            // The date of last modification of the resource (managed by the system)
            'updated_at' => $this->updated_at,

            // The language relationship (LanguageResource)
            'language' => new LanguageResource($this->whenLoaded('language')),
            // The context relationship (ContextResource)
            'context' => new ContextResource($this->whenLoaded('context')),
            // The parent collection (CollectionResource)
            'parent' => new CollectionResource($this->whenLoaded('parent')),
            // Child collections (CollectionResource[])
            'children' => CollectionResource::collection($this->whenLoaded('children')),
            // Translations for this collection (CollectionTranslationResource[])
            'translations' => CollectionTranslationResource::collection($this->whenLoaded('translations')),
            // Partners associated with this collection (PartnerResource[])
            'partners' => PartnerResource::collection($this->whenLoaded('partners')),
            // Items associated with this collection - primary relationship (ItemResource[])
            'items' => ItemResource::collection($this->whenLoaded('items')),
            // Items attached to this collection via many-to-many relationship (ItemResource[])
            'attachedItems' => ItemResource::collection($this->whenLoaded('attachedItems')),

            // The number of items in this collection (computed)
            'items_count' => $this->when($this->relationLoaded('items'), fn () => $this->items->count()),
            'attached_items_count' => $this->when($this->relationLoaded('attachedItems'), fn () => $this->attachedItems->count()),
            'partners_count' => $this->when($this->relationLoaded('partners'), fn () => $this->partners->count()),
            'translations_count' => $this->when($this->relationLoaded('translations'), fn () => $this->translations->count()),
            'children_count' => $this->when($this->relationLoaded('children'), fn () => $this->children->count()),
        ];
    }
}
