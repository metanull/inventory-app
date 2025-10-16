<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource for two-factor authentication challenge response.
 */
class TwoFactorChallengeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'requires_two_factor' => $this->resource['requires_two_factor'] ?? true,
            'available_methods' => $this->resource['available_methods'] ?? [],
            'primary_method' => $this->resource['primary_method'] ?? null,
            'message' => $this->resource['message'] ?? 'Two-factor authentication required.',
        ];
    }
}
