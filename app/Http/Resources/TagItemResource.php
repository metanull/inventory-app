<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // The unique identifier of the tag-item relationship (GUID)
            'id' => $this->id,
            // The tag ID associated with this relationship
            'tag_id' => $this->tag_id,
            // The item ID associated with this relationship
            'item_id' => $this->item_id,
            // The associated tag resource
            'tag' => new TagResource($this->whenLoaded('tag')),
            // The associated item resource
            'item' => new ItemResource($this->whenLoaded('item')),
            // Date of creation
            'created_at' => $this->created_at,
            // Date of last modification
            'updated_at' => $this->updated_at,
        ];
    }
}
