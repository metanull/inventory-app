<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\DetailTranslation
 */
class DetailTranslationResource extends JsonResource
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
            'detail_id' => $this->detail_id,
            'language_id' => $this->language_id,
            'context_id' => $this->context_id,
            'name' => $this->name,
            'alternate_name' => $this->alternate_name,
            'description' => $this->description,
            'author_id' => $this->author_id,
            'text_copy_editor_id' => $this->text_copy_editor_id,
            'translator_id' => $this->translator_id,
            'translation_copy_editor_id' => $this->translation_copy_editor_id,
            'backward_compatibility' => $this->backward_compatibility,
            'extra' => $this->extra,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relationship data
            'detail' => new DetailResource($this->whenLoaded('detail')),
            'language' => new LanguageResource($this->whenLoaded('language')),
            'context' => new ContextResource($this->whenLoaded('context')),
            'author' => new AuthorResource($this->whenLoaded('author')),
            'text_copy_editor' => new AuthorResource($this->whenLoaded('textCopyEditor')),
            'translator' => new AuthorResource($this->whenLoaded('translator')),
            'translation_copy_editor' => new AuthorResource($this->whenLoaded('translationCopyEditor')),
        ];
    }
}
