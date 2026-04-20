<?php

namespace App\Support\Web;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
     * Apply validated sort parameters from the request to the query.
     *
     * Reads 'sort' and 'dir' from the request query string, validates them against
     * the provided whitelist, and falls back to the provided defaults when invalid.
     *
     * @param  array<int, string>  $allowedFields  Whitelist of column names that may be sorted.
     */
    protected function applySort(
        Builder $query,
        Request $request,
        array $allowedFields,
        string $default = 'created_at',
        string $defaultDir = 'desc',
    ): Builder {
        $sort = (string) $request->query('sort', $default);
        if (! in_array($sort, $allowedFields, true)) {
            $sort = $default;
        }

        $dir = strtolower((string) $request->query('dir', $defaultDir));
        if (! in_array($dir, ['asc', 'desc'], true)) {
            $dir = $defaultDir;
        }

        return $query->orderBy($sort, $dir);
    }

    /**
     * Convenience wrapper that applies internal_name search and paginates in one call.
     * Returns array with: paginator instance, the sanitized search string,
     * the resolved sort field, and the resolved sort direction.
     *
     * When $allowedSortFields is non-empty, applySort() is called and the current
     * sort/dir values are returned. When empty, the legacy orderByDesc('created_at')
     * behaviour is preserved so existing callers are unaffected.
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  array<int, string>  $allowedSortFields
     * @return array{0: LengthAwarePaginator, 1: string, 2: string, 3: string}
     */
    protected function searchAndPaginate(
        Builder $baseQuery,
        Request $request,
        array $allowedSortFields = [],
        string $defaultSort = 'created_at',
        string $defaultDir = 'desc',
    ): array {
        $perPage = $this->resolvePerPage($request);
        [$query, $search] = $this->applyInternalNameSearch($baseQuery, $request);

        if ($allowedSortFields !== []) {
            $this->applySort($query, $request, $allowedSortFields, $defaultSort, $defaultDir);
            $sort = (string) $request->query('sort', $defaultSort);
            if (! in_array($sort, $allowedSortFields, true)) {
                $sort = $defaultSort;
            }
            $dir = strtolower((string) $request->query('dir', $defaultDir));
            if (! in_array($dir, ['asc', 'desc'], true)) {
                $dir = $defaultDir;
            }
        } else {
            $query->orderByDesc('created_at');
            $sort = $defaultSort;
            $dir = $defaultDir;
        }

        $paginator = $query->paginate($perPage)->withQueryString();

        return [$paginator, $search, $sort, $dir];
    }
}
