<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'internal_name' => $this->internal_name,
            'phone_number' => $this->phone_number,
            'formatted_phone_number' => $this->formattedPhoneNumber(),
            'fax_number' => $this->fax_number,
            'formatted_fax_number' => $this->formattedFaxNumber(),
            'email' => $this->email,
            'languages' => $this->whenLoaded('languages', function () {
                return $this->languages->map(function ($language) {
                    return [
                        'id' => $language->id,
                        'name' => $language->name,
                        'code' => $language->code,
                        'label' => $language->pivot->label,
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
