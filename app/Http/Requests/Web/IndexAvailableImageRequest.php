<?php

namespace App\Http\Requests\Web;

use App\Support\Web\Lists\AvailableImageListDefinition;
use App\Support\Web\Lists\ListDefinition;

class IndexAvailableImageRequest extends IndexListRequest
{
    protected function createDefinition(): ListDefinition
    {
        return new AvailableImageListDefinition;
    }
}
