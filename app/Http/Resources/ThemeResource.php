<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ThemeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'exhibition_id' => $this->exhibition_id,
            'parent_id' => $this->parent_id,
            'internal_name' => $this->internal_name,
            'backward_compatibility' => $this->backward_compatibility,
            'translations' => ThemeTranslationResource::collection($this->whenLoaded('translations')),
            'subthemes' => ThemeResource::collection($this->whenLoaded('subthemes')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
