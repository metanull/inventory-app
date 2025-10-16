<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource for two-factor authentication status information.
 */
class TwoFactorStatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'two_factor_enabled' => $this->resource['two_factor_enabled'] ?? false,
            'available_methods' => $this->resource['available_methods'] ?? [],
            'primary_method' => $this->resource['primary_method'] ?? null,
            'requires_two_factor' => $this->resource['requires_two_factor'] ?? false,
        ];
    }
}
