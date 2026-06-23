<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Models\ImageUpload;
use Illuminate\Http\Resources\Json\JsonResource;

/** @extends JsonResource<ImageUpload> */
class ImageUploadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // The unique identifier (GUID)
            'id' => $this->resource->id,
            // The path to the picture file
            'path' => $this->resource->path,
            // The original name of the uploaded file
            'name' => $this->resource->name,
            // The file extension of the uploaded file
            'extension' => $this->resource->extension,
            // The MIME type of the uploaded file
            'mime_type' => $this->resource->mime_type,
            // The size of the uploaded file in bytes
            'size' => $this->resource->size,
            // The date of creation of the resource (managed by the system)
            'created_at' => $this->resource->created_at,
            // The date of last modification of the resource (managed by the system)
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
