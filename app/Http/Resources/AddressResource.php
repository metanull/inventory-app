<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Address resource for API responses.
 */
class AddressResource extends JsonResource
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
            'internal_name' => $this->internal_name,
            'country_id' => $this->country_id,
            'languages' => $this->whenLoaded('languages', function () {
                // Returns array of objects: [{id: string, name: string, address: string, description: string|null}, ...]
                return $this->languages->map(function ($language) {
                    return [
                        'id' => $language->id,
                        'name' => $language->name,
                        'address' => $language->pivot->address,
                        'description' => $language->pivot->description,
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
