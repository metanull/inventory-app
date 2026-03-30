<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollectionMediaResource extends JsonResource
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
            'language_id' => $this->language_id,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'url' => $this->url,
            'display_order' => $this->display_order,
            'extra' => $this->extra,
            'backward_compatibility' => $this->backward_compatibility,
            'collection' => new CollectionResource($this->whenLoaded('collection')),
            'language' => new LanguageResource($this->whenLoaded('language')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
