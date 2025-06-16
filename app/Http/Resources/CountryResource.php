<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
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
            // The unique identifier of the country (ISO 3166-1 alpha-3 code)
            'id' => $this->id,
            // The name of the country, it shall only be used internally
            'internal_name' => $this->internal_name,
            // The legacy Id when this country corresponds to a legacy country from the MWNF3 database, nullable
            'backward_compatibility' => $this->backward_compatibility,
            // Date of creation
            'created_at' => $this->created_at,
            // Date of last modification
            'updated_at' => $this->updated_at,
        ];
    }
}
