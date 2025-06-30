<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContextualizationResource extends JsonResource
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
            'context_id' => $this->context_id,
            'item_id' => $this->item_id,
            'detail_id' => $this->detail_id,
            'extra' => $this->extra,
            'internal_name' => $this->internal_name,
            'backward_compatibility' => $this->backward_compatibility,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Include related resources when loaded
            'context' => new ContextResource($this->whenLoaded('context')),
            'item' => new ItemResource($this->whenLoaded('item')),
            'detail' => new DetailResource($this->whenLoaded('detail')),
        ];
    }
}
