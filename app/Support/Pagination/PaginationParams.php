<?php

namespace App\Support\Pagination;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PaginationParams
{
    /**
     * Parse and validate pagination parameters from request.
     *
     * Defaults: page=1, per_page from config; Bounds: per_page 1..max_per_page from config
     *
     * @return array{page:int, per_page:int}
     *
     * @throws ValidationException
     */
    public static function fromRequest(Request $request): array
    {
        $page = (int) ($request->query('page', 1));
        $perPage = (int) $request->query('per_page', config('interface.pagination.default_per_page'));

        // Apply bounds checking using config values
        $maxPerPage = (int) config('interface.pagination.max_per_page');
        $perPage = max(1, min($perPage, $maxPerPage));

        if ($page < 1) {
            throw ValidationException::withMessages([
                'page' => ['Page must be a positive integer (1-based).'],
            ]);
        }

        if ($perPage < 1 || $perPage > $maxPerPage) {
            throw ValidationException::withMessages([
                'per_page' => ["per_page must be between 1 and {$maxPerPage}."],
            ]);
        }

        return [
            'page' => $page,
            'per_page' => $perPage,
        ];
    }
}
