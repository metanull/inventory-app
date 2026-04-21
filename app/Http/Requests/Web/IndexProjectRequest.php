<?php

namespace App\Http\Requests\Web;

use App\Support\Web\Lists\ListDefinition;
use App\Support\Web\Lists\ProjectListDefinition;

class IndexProjectRequest extends IndexListRequest
{
    protected function createDefinition(): ListDefinition
    {
        return new ProjectListDefinition;
    }
}
