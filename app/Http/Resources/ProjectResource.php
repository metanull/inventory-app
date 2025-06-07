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
            'launch_date' => $this->launch_date ? date('Y-m-d', strtotime($this->launch_date)) : null,
            'is_launched' => $this->is_launched,
            'is_enabled' => $this->is_enabled,
            'primary_context_id' => $this->primary_context_id,
            'primary_language_id' => $this->primary_language_id,
            //'primary_context' => $this->whenLoaded('primaryContext'),
            //'primary_language' => $this->whenLoaded('primaryLanguage'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
