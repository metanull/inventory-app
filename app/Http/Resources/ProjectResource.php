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
            // The unique identifier of the project (GUID)
            'id' => $this->id,
            // The name of the project, it shall only be used internally
            'internal_name' => $this->internal_name,
            // The legacy Id when this project corresponds to a legacy project from the MWNF3 database, nullable
            'backward_compatibility' => $this->backward_compatibility,
            // Launch date of the project, nullable
            'launch_date' => $this->launch_date,
            // Indicates if the project has been launched already
            'is_launched' => $this->is_launched,
            // Indicates if the project is enabled (active)
            'is_enabled' => $this->is_enabled,
            // The default context used within the project
            'context' => new ContextResource($this->whenLoaded('context')),
            // The default language used within the project
            'language' => new LanguageResource($this->whenLoaded('language')),
            // Date of creation
            'created_at' => $this->created_at,
            // Date of last modification
            'updated_at' => $this->updated_at,
        ];
    }
}
