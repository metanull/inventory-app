<?php

namespace App\Http\Requests\Web;

use App\Support\Web\Lists\ListDefinition;
use App\Support\Web\Lists\PartnerTranslationListDefinition;

class IndexPartnerTranslationRequest extends IndexListRequest
{
    protected function createDefinition(): ListDefinition
    {
        return new PartnerTranslationListDefinition;
    }
}
