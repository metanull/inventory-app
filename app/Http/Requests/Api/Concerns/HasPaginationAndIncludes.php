<?php

namespace App\Http\Requests\Api\Concerns;

/**
 * Convenience trait that combines pagination and includes support.
 * Simply uses both HasPagination and HasIncludes traits.
 */
trait HasPaginationAndIncludes
{
    use HasIncludes;
    use HasPagination;
}
