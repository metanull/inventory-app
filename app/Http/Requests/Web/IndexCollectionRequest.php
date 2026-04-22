<?php

namespace App\Http\Requests\Web;

use App\Support\Web\Lists\CollectionListDefinition;
use App\Support\Web\Lists\ListDefinition;

class IndexCollectionRequest extends IndexListRequest
{
    protected function createDefinition(): ListDefinition
    {
        return new CollectionListDefinition;
    }
}
