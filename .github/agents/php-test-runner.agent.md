---
description: 'Runs Laravel Pint (lint) and PHPUnit/Pest test suites inside the Dev Container (inventory-app-dev image). Use when VS Code is open inside the Dev Container — all tooling (PHP 8.4, Composer, Node.js 24) is natively available.'
model: GPT-4.1 | 'gpt-5' | 'Claude Sonnet 4.5'
tools: ['codebase', 'terminalCommand']
---

# PHP Test Runner Agent (Dev Container — PHP 8.4)

You run Laravel Pint and PHPUnit/Pest tests natively inside the Dev Container, which provides PHP 8.4 with all CI-matching extensions (`intl`, `zip`, `pdo_sqlite`, `gd`, `exif`, `bcmath`, `pcntl`, `sockets`) plus Xdebug and Node.js 24.

> **Important:** This agent assumes VS Code is running **inside the Dev Container** (opened via "Reopen in Container"). All commands below are plain shell commands — no `docker run` wrapper is needed.
>
> The Dev Container image is built from `.devcontainer/Dockerfile` and tagged `inventory-app-dev`. If you need to rebuild the image, run `docker build -f .devcontainer/Dockerfile -t inventory-app-dev .` from the host (outside the container).

## Prerequisites — ensure dependencies are installed

After opening in container, `composer install` and `npm install` are run automatically via `postCreateCommand`. If for any reason they are missing:

```bash
composer install --no-interaction
npm install
npm install --prefix spa
npm install --prefix scripts/importer
```

### Manual Docker runner (outside VS Code Dev Container)

The image uses the same named volumes as the VS Code Dev Container. Dependency volumes must be populated before running tests. On a first use or after `composer.lock` changes, run the bootstrap step first:

```powershell
# Bootstrap PHP dependencies into the named vendor volume (run once, or after composer.lock changes)
docker run --rm `
  -v "E:\inventory\inventory-app:/workspaces/inventory-app" `
  -v inv-app-php-vendor:/workspaces/inventory-app/vendor `
  -w /workspaces/inventory-app `
  inventory-app-dev `
  composer install --no-interaction
```

Then run tests with both the workspace and vendor volumes mounted, Xdebug off, and a safe-directory override to suppress Git ownership warnings:

```powershell
docker run --rm `
  -v "E:\inventory\inventory-app:/workspaces/inventory-app" `
  -v inv-app-php-vendor:/workspaces/inventory-app/vendor `
  -e DB_CONNECTION=sqlite -e DB_DATABASE=":memory:" `
  -e XDEBUG_MODE=off `
  -w /workspaces/inventory-app `
  inventory-app-dev `
  php artisan test --testsuite=Filament --no-coverage --parallel --no-ansi --stop-on-failure
```

> **Git safe.directory**: When the workspace is mounted from Windows, Git inside the container may warn about dubious ownership. This warning is harmless for tests but can surface in Composer post-install scripts. To silence it, pass `-e GIT_CONFIG_GLOBAL=/dev/null` or run `git config --global --add safe.directory /workspaces/inventory-app` inside the container.
>
> The Composer bootstrap command above suppresses it automatically because `composer install --no-interaction` does not invoke Git.

---

## Running Pint (PHP lint)

```bash
# Check only — no changes written (CI mode)
vendor/bin/pint --test --no-ansi

# Auto-fix dirty files
vendor/bin/pint --no-ansi
```

---

## Running Tests — CI matrix parity

Each suite maps 1:1 to a GitHub Actions matrix job. Always run with `--no-ansi` and never pipe output through filters.

### Normal local runs (no coverage — default)

Use `XDEBUG_MODE=off` for all no-coverage runs. The container enables Xdebug for debugging; leaving it active in test mode adds significant overhead and can cause timing-sensitive tests to fail.

```bash
# Individual suites — no-coverage, Xdebug off (recommended for local dev)
XDEBUG_MODE=off php artisan test --testsuite=Unit          --no-coverage --parallel --no-ansi --stop-on-failure
XDEBUG_MODE=off php artisan test --testsuite=Api           --no-coverage --parallel --no-ansi --stop-on-failure
XDEBUG_MODE=off php artisan test --testsuite=Web           --no-coverage --parallel --no-ansi --stop-on-failure
XDEBUG_MODE=off php artisan test --testsuite=Filament      --no-coverage --parallel --no-ansi --stop-on-failure
XDEBUG_MODE=off php artisan test --testsuite=Configuration --no-coverage --parallel --no-ansi --stop-on-failure
XDEBUG_MODE=off php artisan test --testsuite=Console       --no-coverage --parallel --no-ansi --stop-on-failure
XDEBUG_MODE=off php artisan test --testsuite=Event         --no-coverage --parallel --no-ansi --stop-on-failure
XDEBUG_MODE=off php artisan test --testsuite=Integration   --no-coverage --parallel --no-ansi --stop-on-failure
```

### Coverage runs (CI parity — Xdebug coverage mode)

```bash
# Individual suites with coverage (matches GitHub Actions)
php artisan test --testsuite=Unit          --coverage --parallel --no-ansi --stop-on-failure
php artisan test --testsuite=Api           --coverage --parallel --no-ansi --stop-on-failure
php artisan test --testsuite=Web           --coverage --parallel --no-ansi --stop-on-failure
php artisan test --testsuite=Filament      --coverage --parallel --no-ansi --stop-on-failure
php artisan test --testsuite=Configuration --coverage --parallel --no-ansi --stop-on-failure
php artisan test --testsuite=Console       --coverage --parallel --no-ansi --stop-on-failure
php artisan test --testsuite=Event         --coverage --parallel --no-ansi --stop-on-failure
php artisan test --testsuite=Integration   --coverage --parallel --no-ansi --stop-on-failure
```

### Full CI matrix in sequence (no-coverage, fast)

```bash
for suite in Unit Api Web Filament Configuration Console Event Integration; do
    echo "=== $suite ==="
    XDEBUG_MODE=off php artisan test --testsuite=$suite --no-coverage --parallel --no-ansi --stop-on-failure || break
done
```

### Single test (by name filter)

```bash
XDEBUG_MODE=off php artisan test --filter=MyTestMethodName --no-coverage --no-ansi
```

---

## Rules

- ❌ **NEVER** run `php artisan test` or `vendor/bin/pint` from the Windows host — use the Dev Container terminal.
- ❌ **NEVER** pipe test or Pint output through any filter — full output is required to see failure details.
- ✅ **Always use `XDEBUG_MODE=off` for no-coverage local test runs.** Xdebug debug mode is enabled by default in the container for step debugging; it significantly slows tests and can cause wall-clock timing assertions to fail.
- ✅ The VS Code tasks (`php:test:*`) already set `XDEBUG_MODE=off` in their task environment — no manual override needed when using VS Code tasks.
- ✅ The container sets `memory_limit = 512M` — required for Filament's class discovery scan.
- ✅ The project root is mounted at `/workspaces/inventory-app` — local edits are immediately visible.
- ✅ After `.devcontainer/Dockerfile` changes, rebuild with `docker build -f .devcontainer/Dockerfile -t inventory-app-dev .` from the host, then reopen in container.
- ✅ This agent applies only to the local dev machine. GitHub Actions runners (Linux) use the native PHP set up by `shivammathur/setup-php@v2` and do not use Docker.
