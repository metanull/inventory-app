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
        $data = is_array($this->resource) ? $this->resource : [];

        return [
            'requires_two_factor' => $data['requires_two_factor'] ?? true,
            'available_methods' => $data['available_methods'] ?? [],
            'primary_method' => $data['primary_method'] ?? null,
            'message' => $data['message'] ?? 'Two-factor authentication required.',
        ];
    }
}
