<?php

namespace App\Http\Requests\Web;

use App\Models\Partner;
use App\Support\Web\Lists\ListDefinition;
use App\Support\Web\Lists\PartnerImageListDefinition;

class IndexPartnerImageRequest extends IndexListRequest
{
    protected function createDefinition(): ListDefinition
    {
        return new PartnerImageListDefinition;
    }

    protected function prepareForValidation(): void
    {
        $partner = $this->route('partner');
        if ($partner instanceof Partner) {
            $this->merge(['partner_id' => $partner->id]);
        }
        parent::prepareForValidation();
    }
}
