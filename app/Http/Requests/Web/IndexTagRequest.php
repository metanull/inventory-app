<?php

namespace App\Http\Requests\Web;

use App\Support\Web\Lists\ListDefinition;
use App\Support\Web\Lists\TagListDefinition;

class IndexTagRequest extends IndexListRequest
{
    protected function createDefinition(): ListDefinition
    {
        return new TagListDefinition;
    }
}
