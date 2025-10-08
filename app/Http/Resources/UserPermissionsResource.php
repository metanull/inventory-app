<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property array<string> $resource
 */
class UserPermissionsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, array<int, string>>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->resource,
        ];
    }
}
