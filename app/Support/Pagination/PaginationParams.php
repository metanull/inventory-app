<?php

namespace App\Support\Pagination;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PaginationParams
{
    /**
     * Parse and validate pagination parameters from request.
     *
     * Defaults: page=1, per_page=20; Bounds: per_page 1..100
     *
     * @return array{page:int, per_page:int}
     *
     * @throws ValidationException
     */
    public static function fromRequest(Request $request): array
    {
        $page = (int) ($request->query('page', 1));
        $perPage = (int) ($request->query('per_page', 20));

        if ($page < 1) {
            throw ValidationException::withMessages([
                'page' => ['Page must be a positive integer (1-based).'],
            ]);
        }

        if ($perPage < 1 || $perPage > 100) {
            throw ValidationException::withMessages([
                'per_page' => ['per_page must be between 1 and 100.'],
            ]);
        }

        return [
            'page' => $page,
            'per_page' => $perPage,
        ];
    }
}
