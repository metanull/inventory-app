---
description: 'Runs Laravel Pint (lint) and PHPUnit/Pest test suites via the inventory-app-test Docker container. Use on Windows machines where the native PHP (8.2) lacks the required extensions for the current codebase (Filament 3 requires intl, zip, gd, exif — all pre-built in the image).'
model: GPT-4.1 | 'gpt-5' | 'Claude Sonnet 4.5'
tools: ['codebase', 'terminalCommand']
---

# PHP Test Runner Agent (Docker — PHP 8.4)

You run Laravel Pint and PHPUnit/Pest tests inside the `inventory-app-test` Docker container, which provides PHP 8.4 with all CI-matching extensions (`intl`, `zip`, `pdo_sqlite`, `gd`, `exif`, `bcmath`, `pcntl`, `sodium`). This is the **only** correct way to run PHP tests on this Windows machine; never use the native `php artisan test` command directly.

## Prerequisites — ensure the image exists

```powershell
docker images inventory-app-test
```

If the `inventory-app-test` image is not listed, build it first (one-time cost, ~5 minutes):

```powershell
docker build -f Dockerfile.testing -t inventory-app-test .
```

Rebuild whenever `composer.lock` changes (the vendor layer is cached per lock-file state).

---

## Running Pint (PHP lint)

```powershell
# Check only — no changes written (CI mode)
docker run --rm -v "${PWD}:/app" inventory-app-test vendor/bin/pint --test --no-ansi

# Auto-fix dirty files
docker run --rm -v "${PWD}:/app" inventory-app-test vendor/bin/pint --no-ansi
```

---

## Running Tests — CI matrix parity

Each suite maps 1:1 to a GitHub Actions matrix job. Always run with `--no-ansi` and never pipe output through filters.

The named volume `inv-app-vendor-84` must be seeded from the image before the first run (or after a rebuild):

```powershell
# Seed Linux-native vendor into named volume (once after each image build)
docker volume rm inv-app-vendor-84 2>$null
docker run --rm -v inv-app-vendor-84:/app/vendor inventory-app-test echo "vendor volume initialized"
# Clear any stale Windows bootstrap cache
docker run --rm -v "${PWD}:/app" -v inv-app-vendor-84:/app/vendor `
    -e APP_ENV=testing -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: -e CACHE_STORE=array `
    inventory-app-test php artisan optimize:clear --no-ansi
```

```powershell
# Individual suites (CI parity)
docker run --rm -v "${PWD}:/app" -v inv-app-vendor-84:/app/vendor inventory-app-test php artisan test --testsuite=Unit          --coverage --parallel --no-ansi --stop-on-failure
docker run --rm -v "${PWD}:/app" -v inv-app-vendor-84:/app/vendor inventory-app-test php artisan test --testsuite=Api           --coverage --parallel --no-ansi --stop-on-failure
docker run --rm -v "${PWD}:/app" -v inv-app-vendor-84:/app/vendor inventory-app-test php artisan test --testsuite=Web           --coverage --parallel --no-ansi --stop-on-failure
docker run --rm -v "${PWD}:/app" -v inv-app-vendor-84:/app/vendor inventory-app-test php artisan test --testsuite=Filament      --coverage --parallel --no-ansi --stop-on-failure
docker run --rm -v "${PWD}:/app" -v inv-app-vendor-84:/app/vendor inventory-app-test php artisan test --testsuite=Configuration --coverage --parallel --no-ansi --stop-on-failure
docker run --rm -v "${PWD}:/app" -v inv-app-vendor-84:/app/vendor inventory-app-test php artisan test --testsuite=Console       --coverage --parallel --no-ansi --stop-on-failure
docker run --rm -v "${PWD}:/app" -v inv-app-vendor-84:/app/vendor inventory-app-test php artisan test --testsuite=Event         --coverage --parallel --no-ansi --stop-on-failure
docker run --rm -v "${PWD}:/app" -v inv-app-vendor-84:/app/vendor inventory-app-test php artisan test --testsuite=Integration   --coverage --parallel --no-ansi --stop-on-failure
```

### Full CI matrix in sequence

```powershell
foreach ($suite in @('Unit','Api','Web','Filament','Configuration','Console','Event','Integration')) {
    Write-Host "=== $suite ===" -ForegroundColor Cyan
    docker run --rm -v "${PWD}:/app" -v inv-app-vendor-84:/app/vendor inventory-app-test `
        php artisan test --testsuite=$suite --coverage --parallel --no-ansi --stop-on-failure
    if ($LASTEXITCODE -ne 0) { Write-Error "Suite $suite FAILED"; break }
}
```

### Single test (by name filter)

```powershell
docker run --rm -v "${PWD}:/app" -v inv-app-vendor-84:/app/vendor inventory-app-test `
    php artisan test --filter=MyTestMethodName --no-coverage --no-ansi
```

---

## Rules

- ❌ **NEVER** run `php artisan test` directly — local PHP is 8.2 and lacks required extensions.
- ❌ **NEVER** pipe test or Pint output through `Select-Object`, `grep`, `head`, or any filter — full output is needed to see failure details.
- ✅ The image sets `memory_limit = 512M` (overriding the PHP CLI default of 128M) — required for Filament's class discovery scan.
- ✅ The project root is mounted at `/app` inside the container — local edits are immediately visible without rebuilding.
- ✅ The image vendor layer is baked in; no `composer install` is needed at runtime.
- ✅ After `Dockerfile.testing` changes, rebuild with `docker build -f Dockerfile.testing -t inventory-app-test .`.
- ✅ This agent applies only to the local Windows dev machine. GitHub Actions runners (Linux) use the native PHP set up by `shivammathur/setup-php@v2` and do not use Docker.
