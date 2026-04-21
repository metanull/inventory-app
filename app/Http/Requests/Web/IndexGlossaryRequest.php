<?php

namespace App\Http\Requests\Web;

use App\Support\Web\Lists\GlossaryListDefinition;
use App\Support\Web\Lists\ListDefinition;

class IndexGlossaryRequest extends IndexListRequest
{
    protected function createDefinition(): ListDefinition
    {
        return new GlossaryListDefinition;
    }
}
