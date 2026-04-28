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
```

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

```bash
# Individual suites (CI parity)
php artisan test --testsuite=Unit          --coverage --parallel --no-ansi --stop-on-failure
php artisan test --testsuite=Api           --coverage --parallel --no-ansi --stop-on-failure
php artisan test --testsuite=Web           --coverage --parallel --no-ansi --stop-on-failure
php artisan test --testsuite=Filament      --coverage --parallel --no-ansi --stop-on-failure
php artisan test --testsuite=Configuration --coverage --parallel --no-ansi --stop-on-failure
php artisan test --testsuite=Console       --coverage --parallel --no-ansi --stop-on-failure
php artisan test --testsuite=Event         --coverage --parallel --no-ansi --stop-on-failure
php artisan test --testsuite=Integration   --coverage --parallel --no-ansi --stop-on-failure
```

### Full CI matrix in sequence

```bash
for suite in Unit Api Web Filament Configuration Console Event Integration; do
    echo "=== $suite ==="
    php artisan test --testsuite=$suite --coverage --parallel --no-ansi --stop-on-failure || break
done
```

### Single test (by name filter)

```bash
php artisan test --filter=MyTestMethodName --no-coverage --no-ansi
```

---

## Rules

- ❌ **NEVER** run `php artisan test` or `vendor/bin/pint` from the Windows host — use the Dev Container terminal.
- ❌ **NEVER** pipe test or Pint output through any filter — full output is required to see failure details.
- ✅ The container sets `memory_limit = 512M` — required for Filament's class discovery scan.
- ✅ The project root is mounted at `/workspaces/inventory-app` — local edits are immediately visible.
- ✅ After `.devcontainer/Dockerfile` changes, rebuild with `docker build -f .devcontainer/Dockerfile -t inventory-app-dev .` from the host, then reopen in container.
- ✅ This agent applies only to the local dev machine. GitHub Actions runners (Linux) use the native PHP set up by `shivammathur/setup-php@v2` and do not use Docker.
