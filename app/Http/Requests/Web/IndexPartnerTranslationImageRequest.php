<?php

namespace App\Http\Requests\Web;

use App\Models\PartnerTranslation;
use App\Support\Web\Lists\ListDefinition;
use App\Support\Web\Lists\PartnerTranslationImageListDefinition;

class IndexPartnerTranslationImageRequest extends IndexListRequest
{
    protected function createDefinition(): ListDefinition
    {
        return new PartnerTranslationImageListDefinition;
    }

    protected function prepareForValidation(): void
    {
        $partnerTranslation = $this->route('partner_translation');
        if ($partnerTranslation instanceof PartnerTranslation) {
            $this->merge(['partner_translation_id' => $partnerTranslation->id]);
        }
        parent::prepareForValidation();
    }
}
