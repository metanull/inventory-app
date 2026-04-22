<?php

namespace App\Http\Requests\Web;

use App\Support\Web\Lists\CollectionTranslationListDefinition;
use App\Support\Web\Lists\ListDefinition;

class IndexCollectionTranslationRequest extends IndexListRequest
{
    protected function createDefinition(): ListDefinition
    {
        return new CollectionTranslationListDefinition;
    }
}
