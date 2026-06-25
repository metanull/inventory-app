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
        $data = is_array($this->resource) ? $this->resource : [];

        return [
            'status' => $data['status'] ?? 'unknown',
            'checks' => $data['checks'] ?? [],
            'timestamp' => $data['timestamp'] ?? now()->toISOString(),
        ];
    }
}
