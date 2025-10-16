<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource for conversion responses (markdown to HTML, HTML to markdown, etc.).
 */
class ConversionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $result = [
            'success' => $this->resource['success'] ?? true,
        ];

        if (isset($this->resource['message'])) {
            $result['message'] = $this->resource['message'];
        }

        if (isset($this->resource['data'])) {
            $result['data'] = $this->resource['data'];
        }

        if (isset($this->resource['error'])) {
            $result['error'] = $this->resource['error'];
        }

        if (isset($this->resource['errors'])) {
            $result['errors'] = $this->resource['errors'];
        }

        return $result;
    }
}
