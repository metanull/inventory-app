<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource for application information including version and health status.
 */
class AppInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'application' => $this->resource['application'] ?? [],
            'health' => $this->resource['health'] ?? [],
            'timestamp' => $this->resource['timestamp'] ?? now()->toISOString(),
        ];
    }
}
