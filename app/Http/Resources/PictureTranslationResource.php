<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PictureTranslationResource extends JsonResource
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
            // The picture this translation belongs to (PictureResource id)
            'picture_id' => $this->picture_id,
            // The language of this translation (LanguageResource id)
            'language_id' => $this->language_id,
            // The context of this translation (ContextResource id)
            'context_id' => $this->context_id,
            // The description of the picture translation
            'description' => $this->description,
            // The caption of the picture translation
            'caption' => $this->caption,
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
        ];
    }
}
