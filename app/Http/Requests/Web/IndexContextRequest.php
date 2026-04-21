<?php

namespace App\Http\Requests\Web;

use App\Support\Web\Lists\ContextListDefinition;
use App\Support\Web\Lists\ListDefinition;

class IndexContextRequest extends IndexListRequest
{
    protected function createDefinition(): ListDefinition
    {
        return new ContextListDefinition;
    }
}
