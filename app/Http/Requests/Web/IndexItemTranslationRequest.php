<?php

namespace App\Http\Requests\Web;

use App\Support\Web\Lists\ItemTranslationListDefinition;
use App\Support\Web\Lists\ListDefinition;

class IndexItemTranslationRequest extends IndexListRequest
{
    protected function createDefinition(): ListDefinition
    {
        return new ItemTranslationListDefinition;
    }
}
