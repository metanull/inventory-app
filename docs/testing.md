# Test Organization

This project separates tests into different categories to optimize CI performance and maintain clear boundaries between unit, feature, and integration testing.

## Test Suites

### Core Backend Tests (CI Suite)

- **Unit Tests** (`tests/Unit/`) - Pure unit tests with no external dependencies
- **Feature Tests** (`tests/Feature/`) - API and feature tests using database
- **CI/CD Tests** (`tests/CiCd/`) - Infrastructure and deployment tests

These tests run with: `composer ci-test` or `php artisan test --testsuite=CI`

### Integration Tests (Separate Suite)

- **Frontend Integration** (`tests/Integration/Frontend/`) - Tests requiring compiled frontend assets

These tests run with: `composer ci-test:integration` or `php artisan test --testsuite=Integration`

## Why This Separation?

### Performance

- **Backend validation** can run in parallel without waiting for frontend build
- **CI pipeline** completes ~40-50% faster with parallel execution
- **Resource efficiency** - no unnecessary frontend compilation for API tests

### Reliability

- **No false failures** from missing Vite manifests in pure API tests
- **Clear test boundaries** - backend tests don't depend on frontend state
- **Better isolation** - easier to identify the source of test failures

## Running Tests

### In Development

```powershell
# Backend tests only (fast)
composer ci-test

# Frontend integration tests (requires npm run build first)
npm run build
composer ci-test:integration

# All tests
composer ci-test:all
```

### In CI Pipeline

- **Backend Validation Job**: Runs `composer ci-test` (excludes Integration)
- **Frontend Validation Job**: Runs frontend linting and tests
- **Integration tests**: Not included in CI (run manually when needed)

## Adding New Tests

### Add to Backend Suite (most common)

Place in `tests/Unit/`, `tests/Feature/`, or `tests/CiCd/`

### Add to Integration Suite (only if needed)

Place in `tests/Integration/` when test requires:

- Compiled frontend assets (Vite manifest)
- Full frontend-backend integration
- Browser automation

## Show endpoint test convention

Every API entity with a Show endpoint must include exactly two structure tests to enforce consistent API contracts:

- test_show_returns_the_default_structure_without_relations
  - Calls the Show endpoint without any include query param.
  - Asserts only core fields for the resource. Relations must not appear unless their resource always includes them as primitives.

- test_show_returns_the_expected_structure_with_all_relations_loaded
  - Calls the Show endpoint with include=... for all relations allowed for that entity.
  - Asserts the presence of those relation keys in the response (using whenLoaded semantics in JsonResources).

When picking the include list, use App\Support\Includes\AllowList::for('<entity>') as the source of truth. If an entity does not define allow-list entries, the “all relations” test may be omitted or limited to the relationships that are meaningful in that context.

Notes:

- Avoid asserting nested structures deeply in these two tests; focus on top-level presence and serialization shape.
- For entities where certain relations are computed or conditionally included, assert structure keys only after requesting the respective include(s).
- Legacy controllers that still eager-load relations should be migrated over time; until then, prefer the include-based pattern in tests to avoid accidental defaults.

## Configuration

Test suites are defined in [`phpunit.xml`](phpunit.xml):

- **CI Suite**: Excludes `tests/Integration/`
- **Integration Suite**: Only `tests/Integration/`
- **Default**: All tests when no suite specified
