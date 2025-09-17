<?php

namespace App\Support\Web;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Reusable helper methods for Blade CRUD list pages implementing
 * simple internal_name search and constrained per-page pagination.
 *
 * Keep intentionally framework-light (no dependencies on specific models)
 * so controllers can compose these behaviors while retaining clarity.
 */
trait SearchAndPaginate
{
    /**
     * Resolve a sanitized per-page value from the request constrained
     * by interface pagination configuration.
     */
    protected function resolvePerPage(Request $request): int
    {
        $perPage = (int) $request->query('per_page', config('interface.pagination.default_per_page'));
        $perPage = max(1, min($perPage, (int) config('interface.pagination.max_per_page')));

        return $perPage;
    }

    /**
     * Apply a basic LIKE search on internal_name if a non-empty 'q' query param exists.
     * Returns the (possibly) modified Builder and the sanitized search string.
     *
     * @return array{0: Builder, 1: string}
     */
    protected function applyInternalNameSearch(Builder $query, Request $request): array
    {
        $search = trim((string) $request->query('q'));
        if ($search !== '') {
            $query->where('internal_name', 'LIKE', "%{$search}%");
        }

        return [$query, $search];
    }

    /**
     * Convenience wrapper that applies internal_name search and paginates in one call.
     * Returns array with: paginator instance and the sanitized search string.
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @return array{0: \Illuminate\Contracts\Pagination\LengthAwarePaginator, 1: string}
     */
    protected function searchAndPaginate(Builder $baseQuery, Request $request): array
    {
        $perPage = $this->resolvePerPage($request);
        [$query, $search] = $this->applyInternalNameSearch($baseQuery, $request);
        $paginator = $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();

        return [$paginator, $search];
    }
}
