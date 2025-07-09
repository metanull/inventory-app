<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExhibitionTranslationResource extends JsonResource
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
            'language_id' => $this->language_id,
            'context_id' => $this->context_id,
            'title' => $this->title,
            'description' => $this->description,
            'url' => $this->url,
            'backward_compatibility' => $this->backward_compatibility,
            'extra' => $this->extra,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
