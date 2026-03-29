<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DynastyTranslationResource extends JsonResource
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
            'dynasty_id' => $this->dynasty_id,
            'language_id' => $this->language_id,
            'name' => $this->name,
            'also_known_as' => $this->also_known_as,
            'area' => $this->area,
            'history' => $this->history,
            'date_description_ah' => $this->date_description_ah,
            'date_description_ad' => $this->date_description_ad,
            'author_id' => $this->author_id,
            'text_copy_editor_id' => $this->text_copy_editor_id,
            'translator_id' => $this->translator_id,
            'translation_copy_editor_id' => $this->translation_copy_editor_id,
            'dynasty' => new DynastyResource($this->whenLoaded('dynasty')),
            'language' => new LanguageResource($this->whenLoaded('language')),
            'author' => new AuthorResource($this->whenLoaded('author')),
            'text_copy_editor' => new AuthorResource($this->whenLoaded('textCopyEditor')),
            'translator' => new AuthorResource($this->whenLoaded('translator')),
            'translation_copy_editor' => new AuthorResource($this->whenLoaded('translationCopyEditor')),
            'backward_compatibility' => $this->backward_compatibility,
            'extra' => $this->extra,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
