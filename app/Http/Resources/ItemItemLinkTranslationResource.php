<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\ItemItemLinkTranslation
 */
class ItemItemLinkTranslationResource extends JsonResource
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
            // The item-item link this translation belongs to (ItemItemLinkResource id)
            'item_item_link_id' => $this->item_item_link_id,
            // The language of this translation (LanguageResource id)
            'language_id' => $this->language_id,
            // The description of the link (source → target direction)
            'description' => $this->description,
            // The reciprocal description of the link (target → source direction)
            'reciprocal_description' => $this->reciprocal_description,
            // The Id(s) of matching resource in the legacy system (if any)
            'backward_compatibility' => $this->backward_compatibility,
            // The date of creation of the resource (managed by the system)
            'created_at' => $this->created_at,
            // The date of last modification of the resource (managed by the system)
            'updated_at' => $this->updated_at,

            // The item-item link relationship (ItemItemLinkResource)
            'item_item_link' => new ItemItemLinkResource($this->whenLoaded('itemItemLink')),
            // The language relationship (LanguageResource)
            'language' => new LanguageResource($this->whenLoaded('language')),
        ];
    }
}
