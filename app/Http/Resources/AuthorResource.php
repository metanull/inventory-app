<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthorResource extends JsonResource
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
            // The name of the author
            'name' => $this->name,
            // A name for this resource, for internal use only.
            'internal_name' => $this->internal_name,
            // The Id(s) of matching resource in the legacy system (if any).
            'backward_compatibility' => $this->backward_compatibility,
            // The date of creation of the resource (managed by the system)
            'created_at' => $this->created_at,
            // The date of last modification of the resource (managed by the system)
            'updated_at' => $this->updated_at,
        ];
    }
}
