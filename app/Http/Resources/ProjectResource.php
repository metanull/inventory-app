<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'internal_name' => $this->internal_name,
            'backward_compatibility' => $this->backward_compatibility,
            'launch_date' => $this->launch_date, // ? date('Y-m-d', strtotime($this->launch_date)) : null,
            'is_launched' => $this->is_launched,
            'is_enabled' => $this->is_enabled,
            //'context_id' => $this->context_id,
            //'language_id' => $this->language_id,
            'context' => new ContextResource($this->whenLoaded('context')),
            'language' => new LanguageResource($this->whenLoaded('language')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
