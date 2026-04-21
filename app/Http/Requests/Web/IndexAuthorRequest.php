<?php

namespace App\Http\Requests\Web;

use App\Support\Web\Lists\AuthorListDefinition;
use App\Support\Web\Lists\ListDefinition;

class IndexAuthorRequest extends IndexListRequest
{
    protected function createDefinition(): ListDefinition
    {
        return new AuthorListDefinition;
    }
}
