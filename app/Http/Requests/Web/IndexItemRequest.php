<?php

namespace App\Http\Requests\Web;

use App\Support\Web\Lists\ItemListDefinition;
use App\Support\Web\Lists\ListDefinition;

class IndexItemRequest extends IndexListRequest
{
    protected function createDefinition(): ListDefinition
    {
        return new ItemListDefinition;
    }
}
