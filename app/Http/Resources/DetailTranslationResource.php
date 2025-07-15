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
            // The unique identifier (GUID)
            'id' => $this->id,
            // The detail this translation belongs to (DetailResource id)
            'detail_id' => $this->detail_id,
            // The language of this translation (LanguageResource id)
            'language_id' => $this->language_id,
            // The context of this translation (ContextResource id)
            'context_id' => $this->context_id,
            // The name of the detail translation
            'name' => $this->name,
            // The alternate name of the detail translation
            'alternate_name' => $this->alternate_name,
            // The description of the detail translation
            'description' => $this->description,
            // The author of the translation (AuthorResource id)
            'author_id' => $this->author_id,
            // The text copy editor of the translation (UserResource id)
            'text_copy_editor_id' => $this->text_copy_editor_id,
            // The translator of the translation (UserResource id)
            'translator_id' => $this->translator_id,
            // The translation copy editor of the translation (UserResource id)
            'translation_copy_editor_id' => $this->translation_copy_editor_id,
            // The Id(s) of matching resource in the legacy system (if any).
            'backward_compatibility' => $this->backward_compatibility,
            // Extra data for translation (object, may be null)
            'extra' => $this->extra,
            // The date of creation of the resource (managed by the system)
            'created_at' => $this->created_at,
            // The date of last modification of the resource (managed by the system)
            'updated_at' => $this->updated_at,

            // The detail relationship (DetailResource)
            'detail' => new DetailResource($this->whenLoaded('detail')),
            // The language relationship (LanguageResource)
            'language' => new LanguageResource($this->whenLoaded('language')),
            // The context relationship (ContextResource)
            'context' => new ContextResource($this->whenLoaded('context')),
            // The author relationship (AuthorResource)
            'author' => new AuthorResource($this->whenLoaded('author')),
            // The text copy editor relationship (AuthorResource)
            'text_copy_editor' => new AuthorResource($this->whenLoaded('textCopyEditor')),
            // The translator relationship (AuthorResource)
            'translator' => new AuthorResource($this->whenLoaded('translator')),
            // The translation copy editor relationship (AuthorResource)
            'translation_copy_editor' => new AuthorResource($this->whenLoaded('translationCopyEditor')),
        ];
    }
}
