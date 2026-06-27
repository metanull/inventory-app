<?php

namespace App\Support\Pagination;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
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
        $pageRaw = $request->query('page');
        $page = is_numeric($pageRaw) ? (int) $pageRaw : 1;
        $perPageRaw = $request->query('per_page');
        $perPage = is_numeric($perPageRaw) ? (int) $perPageRaw : Config::integer('interface.pagination.default_per_page');

        // Apply bounds checking using config values
        $maxPerPage = Config::integer('interface.pagination.max_per_page');
        $perPage = max(1, min($perPage, $maxPerPage));

        if ($page < 1) {
            throw ValidationException::withMessages([
                'page' => ['Page must be a positive integer (1-based).'],
            ]);
        }

        return [
            'page' => $page,
            'per_page' => $perPage,
        ];
    }
}
