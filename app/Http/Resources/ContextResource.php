<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContextResource extends JsonResource
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
            // The unique identifier of the context (GUID)
            'id' => $this->id,
            // The name of the context, it shall only be used internally
            'internal_name' => $this->internal_name,
            // The legacy Id when this context corresponds to a legacy context from the MWNF3 database, nullable
            'backward_compatibility' => $this->backward_compatibility,
            // Indicates if this context is the default one. There is one single default context for the entire database.
            'is_default' => $this->is_default,
            // Date of creation
            'created_at' => $this->created_at,
            // Date of last modification
            'updated_at' => $this->updated_at,
        ];
    }
}
