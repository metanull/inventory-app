<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContributorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'collection_id' => $this->collection_id,
            'category' => $this->category,
            'display_order' => $this->display_order,
            'visible' => $this->visible,
            'backward_compatibility' => $this->backward_compatibility,
            'internal_name' => $this->internal_name,
            // Relationships
            'collection' => new CollectionResource($this->whenLoaded('collection')),
            'translations' => ContributorTranslationResource::collection($this->whenLoaded('translations')),
            'images' => ContributorImageResource::collection($this->whenLoaded('contributorImages')),
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
