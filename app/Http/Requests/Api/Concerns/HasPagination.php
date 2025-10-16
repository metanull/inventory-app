<?php

namespace App\Http\Requests\Api\Concerns;

use App\Support\Pagination\PaginationParams;

/**
 * Provides pagination support for API requests.
 */
trait HasPagination
{
    /**
     * Get validated pagination parameters.
     *
     * @return array{page:int, per_page:int}
     */
    public function getPaginationParams(): array
    {
        return PaginationParams::fromRequest($this);
    }
}
