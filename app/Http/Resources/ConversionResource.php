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
        $data = is_array($this->resource) ? $this->resource : [];

        $result = [
            'success' => $data['success'] ?? true,
        ];

        if (isset($data['message'])) {
            $result['message'] = $data['message'];
        }

        if (isset($data['data'])) {
            $result['data'] = $data['data'];
        }

        if (isset($data['error'])) {
            $result['error'] = $data['error'];
        }

        if (isset($data['errors'])) {
            $result['errors'] = $data['errors'];
        }

        return $result;
    }
}
