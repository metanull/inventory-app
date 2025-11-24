<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // The unique identifier of the tag (GUID)
            'id' => $this->id,
            // The name of the tag, it shall only be used internally
            'internal_name' => $this->internal_name,
            // The category of the tag (keyword, material, artist, dynasty), nullable
            'category' => $this->category,
            // The language of the tag (ISO 639-3 code), nullable
            'language_id' => $this->language_id,
            // The legacy Id when this tag corresponds to a legacy tag from the previous database, nullable
            'backward_compatibility' => $this->backward_compatibility,
            // The description of the tag
            'description' => $this->description,
            // Date of creation
            'created_at' => $this->created_at,
            // Date of last modification
            'updated_at' => $this->updated_at,
        ];
    }
}
