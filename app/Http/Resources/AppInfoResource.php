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
        $data = is_array($this->resource) ? $this->resource : [];

        return [
            'application' => $data['application'] ?? [],
            'health' => $data['health'] ?? [],
            'timestamp' => $data['timestamp'] ?? now()->toISOString(),
        ];
    }
}
