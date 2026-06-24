<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Models\Country;
use App\Http\Resources\BaseJsonResource;

/** @extends BaseJsonResource<Country> */
class CountryResource extends BaseJsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // The unique identifier (ISO 3166-1 alpha-3 code)
            'id' => $this->resource->id,
            // A name for this resource, for internal use only.
            'internal_name' => $this->resource->internal_name,
            // The Id(s) of matching resource in the legacy system (if any).
            'backward_compatibility' => $this->resource->backward_compatibility,
            // The date of creation of the resource (managed by the system)
            'created_at' => $this->resource->created_at,
            // The date of last modification of the resource (managed by the system)
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
