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
            // The unique identifier (GUID)
            'id' => $this->id,
            // A name for this resource, for internal use only.
            'internal_name' => $this->internal_name,
            // The Id(s) of matching resource in the legacy system (if any).
            'backward_compatibility' => $this->backward_compatibility,
            // Launch date of the project, nullable
            'launch_date' => $this->launch_date,
            // Indicates if the project has been launched already
            'is_launched' => $this->is_launched,
            // Indicates if the project is enabled (active)
            'is_enabled' => $this->is_enabled,
            // The default context used within the project (ContextResource)
            'context' => new ContextResource($this->whenLoaded('context')),
            // The default language used within the project (LanguageResource)
            'language' => new LanguageResource($this->whenLoaded('language')),
            // The date of creation of the resource (managed by the system)
            'created_at' => $this->created_at,
            // The date of last modification of the resource (managed by the system)
            'updated_at' => $this->updated_at,
        ];
    }
}
