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
            'id' => $this->id,
            'picture_id' => $this->picture_id,
            'language_id' => $this->language_id,
            'context_id' => $this->context_id,
            'description' => $this->description,
            'caption' => $this->caption,
            'author_id' => $this->author_id,
            'text_copy_editor_id' => $this->text_copy_editor_id,
            'translator_id' => $this->translator_id,
            'translation_copy_editor_id' => $this->translation_copy_editor_id,
            'backward_compatibility' => $this->backward_compatibility,
            'extra' => $this->extra,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
