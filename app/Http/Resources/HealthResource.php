<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource for health check information.
 */
class HealthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'status' => $this->resource['status'] ?? 'unknown',
            'checks' => $this->resource['checks'] ?? [],
            'timestamp' => $this->resource['timestamp'] ?? now()->toISOString(),
        ];
    }
}
