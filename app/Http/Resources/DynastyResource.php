<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DynastyResource extends JsonResource
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
            'from_ah' => $this->from_ah,
            'to_ah' => $this->to_ah,
            'from_ad' => $this->from_ad,
            'to_ad' => $this->to_ad,
            'backward_compatibility' => $this->backward_compatibility,
            'translations' => DynastyTranslationResource::collection($this->whenLoaded('translations')),
            'items' => ItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
