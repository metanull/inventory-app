<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthorTranslationResource extends JsonResource
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
            'author_id' => $this->author_id,
            'language_id' => $this->language_id,
            'context_id' => $this->context_id,
            'curriculum' => $this->curriculum,
            'author' => new AuthorResource($this->whenLoaded('author')),
            'language' => new LanguageResource($this->whenLoaded('language')),
            'context' => new ContextResource($this->whenLoaded('context')),
            'backward_compatibility' => $this->backward_compatibility,
            'extra' => $this->extra,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
