<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageUploadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            // The unique identifier of the picture (GUID)
            'id' => $this->id,
            // The path to the picture file
            'path' => $this->path,
            // The original name of the uploaded file
            'name' => $this->name,
            // The file extension of the uploaded file
            'extension' => $this->extension,
            // The MIME type of the uploaded file
            'mime_type' => $this->mime_type,
            // The size of the uploaded file in bytes
            'size' => $this->size,
            // Date of creation
            'created_at' => $this->created_at,
            // Date of last modification
            'updated_at' => $this->updated_at,
        ];
    }
}
