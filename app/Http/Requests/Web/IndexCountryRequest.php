<?php

namespace App\Http\Requests\Web;

use App\Support\Web\Lists\CountryListDefinition;
use App\Support\Web\Lists\ListDefinition;

class IndexCountryRequest extends IndexListRequest
{
    protected function createDefinition(): ListDefinition
    {
        return new CountryListDefinition;
    }
}
