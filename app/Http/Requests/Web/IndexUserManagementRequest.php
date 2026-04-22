<?php

namespace App\Http\Requests\Web;

use App\Support\Web\Lists\ListDefinition;
use App\Support\Web\Lists\UserManagementListDefinition;

class IndexUserManagementRequest extends IndexListRequest
{
    protected function createDefinition(): ListDefinition
    {
        return new UserManagementListDefinition;
    }
}
