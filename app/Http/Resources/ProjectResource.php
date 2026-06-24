<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Http\Resources\BaseJsonResource;

/** @extends BaseJsonResource<Project> */
class ProjectResource extends BaseJsonResource
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
            'id' => $this->resource->id,
            // A name for this resource, for internal use only.
            'internal_name' => $this->resource->internal_name,
            // The Id(s) of matching resource in the legacy system (if any).
            'backward_compatibility' => $this->resource->backward_compatibility,
            // Launch date of the project, nullable
            'launch_date' => $this->resource->launch_date,
            // Indicates if the project has been launched already
            'is_launched' => $this->resource->is_launched,
            // Indicates if the project is enabled (active)
            'is_enabled' => $this->resource->is_enabled,
            // The default context used within the project (ContextResource)
            'context' => new ContextResource($this->whenLoaded('context')),
            // The default language used within the project (LanguageResource)
            'language' => new LanguageResource($this->whenLoaded('language')),
            // The date of creation of the resource (managed by the system)
            'created_at' => $this->resource->created_at,
            // The date of last modification of the resource (managed by the system)
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
