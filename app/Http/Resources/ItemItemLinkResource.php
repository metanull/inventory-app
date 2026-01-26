<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemItemLinkResource extends JsonResource
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
            // The source item ID (the item initiating the link)
            'source_id' => $this->source_id,
            // The target item ID (the item receiving the link)
            'target_id' => $this->target_id,
            // The context ID (the context in which the link exists)
            'context_id' => $this->context_id,
            // The source item (ItemResource)
            'source' => new ItemResource($this->whenLoaded('source')),
            // The target item (ItemResource)
            'target' => new ItemResource($this->whenLoaded('target')),
            // The context (ContextResource)
            'context' => new ContextResource($this->whenLoaded('context')),
            // The translations (ItemItemLinkTranslationResource collection)
            'translations' => ItemItemLinkTranslationResource::collection($this->whenLoaded('translations')),
            // The date of creation of the resource (managed by the system)
            'created_at' => $this->created_at,
            // The date of last modification of the resource (managed by the system)
            'updated_at' => $this->updated_at,
        ];
    }
}
