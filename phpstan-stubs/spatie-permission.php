<?php

namespace Spatie\Permission\Models;

/**
 * @property string|null $description
 */
class Permission {}

/**
 * @property string $name
 * @property string|null $description
 */
class Role
{
    /** @param string|Permission ...$permissions */
    public function givePermissionTo(mixed ...$permissions): static {}
}
