<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PictureResource extends JsonResource
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
            'id' => $this->id,
            // A name for this resource, for internal use only.
            'internal_name' => $this->internal_name,
            // The Id(s) of matching resource in the legacy system (if any).
            'backward_compatibility' => $this->backward_compatibility,
            // The path to the picture file
            'path' => $this->path,
            // The copyright text associated with the picture
            'copyright_text' => $this->copyright_text,
            // The URL for the copyright information
            'copyright_url' => $this->copyright_url,
            // The original name of the uploaded file
            'upload_name' => $this->upload_name,
            // The file extension of the uploaded file
            'upload_extension' => $this->upload_extension,
            // The MIME type of the uploaded file
            'upload_mime_type' => $this->upload_mime_type,
            // The size of the uploaded file in bytes
            'upload_size' => $this->upload_size,
            // The type of the parent model (Item, Detail, Partner)
            'pictureable_type' => $this->pictureable_type,
            // The ID of the parent model
            'pictureable_id' => $this->pictureable_id,
            // Date of creation
            'created_at' => $this->created_at,
            // Date of last modification
            'updated_at' => $this->updated_at,
        ];
    }
}
