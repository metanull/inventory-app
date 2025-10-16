<?php

namespace App\Http\Requests\Api\Concerns;

use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;

/**
 * Provides include parameter support for API requests.
 */
trait HasIncludes
{
    /**
     * Get validated include parameters.
     *
     * @return array<int, string>
     */
    public function getIncludeParams(): array
    {
        return IncludeParser::fromRequest($this, AllowList::for($this->getIncludeAllowlistKey()));
    }

    /**
     * Get the include allowlist key for this resource.
     * Must be implemented by the using class to specify which resource type's includes are allowed.
     */
    abstract protected function getIncludeAllowlistKey(): string;
}
