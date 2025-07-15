<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Gallery Translation Resource
 *
 * Transforms GalleryTranslation model data for API responses.
 * Includes relationships and computed attributes for API consumption.
 */
class GalleryTranslationResource extends JsonResource
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
            // The gallery this translation belongs to (GalleryResource id)
            'gallery_id' => $this->gallery_id,
            // The language of this translation (LanguageResource id)
            'language_id' => $this->language_id,
            // The context of this translation (ContextResource id)
            'context_id' => $this->context_id,
            // The title of the gallery translation
            'title' => $this->title,
            // The description of the gallery translation
            'description' => $this->description,
            // The URL for the gallery translation
            'url' => $this->url,
            // The Id(s) of matching resource in the legacy system (if any).
            'backward_compatibility' => $this->backward_compatibility,
            // Extra data for translation (object, may be null)
            'extra' => $this->extra,
            // The date of creation of the resource (managed by the system)
            'created_at' => $this->created_at,
            // The date of last modification of the resource (managed by the system)
            'updated_at' => $this->updated_at,

            // The gallery relationship (GalleryResource)
            'gallery' => new GalleryResource($this->whenLoaded('gallery')),
            // The language relationship (LanguageResource)
            'language' => new LanguageResource($this->whenLoaded('language')),
            // The context relationship (ContextResource)
            'context' => new ContextResource($this->whenLoaded('context')),
        ];
    }
}
