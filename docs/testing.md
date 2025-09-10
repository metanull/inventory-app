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

## Configuration

Test suites are defined in [`phpunit.xml`](phpunit.xml):
- **CI Suite**: Excludes `tests/Integration/`
- **Integration Suite**: Only `tests/Integration/`
- **Default**: All tests when no suite specified
