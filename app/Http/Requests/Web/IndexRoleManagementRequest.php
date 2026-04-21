<?php

namespace App\Http\Requests\Web;

use App\Support\Web\Lists\ListDefinition;
use App\Support\Web\Lists\RoleManagementListDefinition;

class IndexRoleManagementRequest extends IndexListRequest
{
    protected function createDefinition(): ListDefinition
    {
        return new RoleManagementListDefinition;
    }
}
