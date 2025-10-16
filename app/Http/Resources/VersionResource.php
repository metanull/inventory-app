<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource for application version information.
 */
class VersionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'repository' => $this->resource['repository'] ?? null,
            'build_timestamp' => $this->resource['build_timestamp'] ?? null,
            'repository_url' => $this->resource['repository_url'] ?? null,
            'api_client_version' => $this->resource['api_client_version'] ?? null,
            'app_version' => $this->resource['app_version'] ?? '1.0.0-dev',
            'commit_sha' => $this->resource['commit_sha'] ?? null,
        ];
    }
}
