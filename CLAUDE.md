# Inventory App — Project Instructions

## Core Rules

These rules apply to all code and documentation changes, regardless of scope.

1. Never generate or modify code unless explicitly instructed.
2. Never generate or modify documentation unless explicitly instructed.
3. When in doubt about requirements, ask for clarification before proceeding.
4. Always present multiple approaches when they exist; recommend the one that best aligns with existing project patterns.
5. Never assume user intentions; ask for explicit confirmation when in doubt.
6. Always verify if requested functionality already exists before implementing new code.
7. Never introduce new libraries, dependencies, or frameworks without explicit approval.
8. Never duplicate existing code; always reuse or refactor.
9. Only add comments in code to explain non-obvious business logic. Never comment to explain what a request was about.
10. Always follow established project patterns and conventions exactly.
11. Never alter code to fit testing needs; tests must adapt to existing code.
12. Never write tests that depend on external data or state; tests must be self-contained and isolated.
13. Never alter existing database migrations; create new migrations for changes.
14. On Windows, use PowerShell syntax when running host shell commands (not Linux escaping).
15. Always write database-agnostic migrations and code. Production uses MySQL/MariaDB, devcontainer uses SQLite, other Laravel-supported engines are possible.
16. If you cannot complete a task according to these rules, stop and report the exact point of failure. Do not attempt workarounds.
17. If you modify existing code, never keep the original as a fallback or comment it out — remove it entirely.
18. Never implement fallback mechanisms without explicit approval. Code must fail fast.
19. Functions and classes must respect single-responsibility principle.
20. Code must respect DRY and KISS — avoid duplication and unnecessary complexity.
21. Code must be testable; write tests for all new features and bug fixes.
22. Tests must cover business logic only — do not test the framework or third-party libraries.
23. Never pipe test or build commands through any output filter (`Select-Object`, `head`, `tail`, `grep`, etc.). Always run unpiped so full output (including stack traces) is visible.

---

## UI Architecture: Filament 3 is the Main UI

The Filament 3 migration is **complete**. `/admin` is the primary interface for all authenticated users. The legacy `/web` (Blade/Livewire) UI remains in the codebase but is discontinued — no new features or fixes should target it. It will be removed in a future cleanup.

1. **`/admin` (Filament 3) is the main UI** for every authenticated role except `Non-verified users`.
2. **Three-tier authorization model**:
   - Tier 1 — Panel gate: `access-admin-panel` Spatie permission grants `/admin` entry. Assigned to `Visitor`, `Regular User`, `Manager of Users`. Never to `Non-verified users`.
   - Tier 2 — Navigation/Resource visibility: per-feature permissions (`view-data`, `manage-users`, `manage-roles`, `manage-settings`, `manage-reference-data`) drive `canViewAny()` and `shouldRegisterNavigation()`.
   - Tier 3 — Record/Action authorization: existing `App\Policies\*` classes, unchanged.
3. **Test placement**: all Filament tests live under `tests/Filament/{Resources,Pages,Panel,Authorization}/`. Never add tests to `tests/Web/` — that suite is frozen pending removal.
4. **Self-service is first-class**: profile, password change, 2FA enrolment, session logout, and account deletion are Filament `ProfilePage` features. Do not reintroduce Jetstream Blade profile pages.
5. `IndexListRequest`, `{Entity}IndexQuery`, `SearchableSelect`, `SearchAndPaginate`, Livewire list components, and `/web/*` routes are **legacy and being removed**. Do not author new code against those patterns. Always use Filament Resource / Relation Manager / Page.

---

## Strict `/admin` ⇄ `/web` Auth Isolation

`/admin` (Filament) and `/web` (Blade/Jetstream/Livewire) auth flows MUST stay strictly isolated. `/web` is discontinued and will be removed in a future cleanup.

- `/admin` is fully Filament-native for login, MFA challenge, and MFA setup. Post-login/challenge/setup redirects resolve **only** to admin panel URLs.
- Fortify remains the underlying auth service layer — used as a service, not as orchestrator.
  - ✅ Allowed in `/admin`: `TwoFactorAuthenticationProvider`, `EnableTwoFactorAuthentication`, `ConfirmTwoFactorAuthentication`, `DisableTwoFactorAuthentication`, `TwoFactorAuthenticatable` trait, `Fortify::currentEncrypter()`, `Features::twoFactorAuthentication()`.
  - ❌ Forbidden in `/admin`: `RedirectIfTwoFactorAuthenticatable`, `AttemptToAuthenticate`, `PrepareAuthenticatedSession`, `LoginResponse`, `TwoFactorLoginResponse`, `TwoFactorChallengeViewResponse`, routes `two-factor.login` / `two-factor.login.store`.
- No shared session markers bridge the two flows.
- No code in `app/Filament/**` may reference `two-factor.login`, `filament.auth.panel`, `login.id`, `RedirectsIfTwoFactorAuthenticatable`, or any Blade view under `resources/views/auth/`.
- Tests for this boundary live under `tests/Filament/` and MUST cover both directional regressions.

---

## Project Overview

**Inventory Management API** — Laravel 12 backend for museum inventory management at Museum With No Frontiers. Monorepo containing:

- **Backend**: Laravel 12, PHP 8.5, REST API + Filament 3 UI (main) + discontinued Blade/Livewire `/web` UI (kept temporarily, pending removal)
- **Documentation Site**: Jekyll static site in `docs/` deployed to GitHub Pages
- **TypeScript API Client**: Auto-generated from OpenAPI spec, published to npm (`api-client/`)
- **SPA Demo**: Vue 3 + TypeScript reference implementation (`spa/`)
- **Data Importer**: PowerShell + TypeScript scripts for legacy data migration (`scripts/importer/`)

### Key Technologies

| Layer | Stack |
|---|---|
| Backend | Laravel 12, PHP 8.5, Eloquent, Sanctum, Filament 3, Spatie Permissions |
| Legacy Web UI | Blade, Livewire, Alpine.js, Tailwind CSS 4, Vite |
| SPA Demo | Vue 3, TypeScript, Pinia, Vue Router, Tailwind CSS 4 |
| Database | MySQL/MariaDB (production), SQLite (devcontainer tests) |
| Testing | Pest (backend), Vitest (frontend) |
| Tooling | Composer, npm, Laravel Pint, ESLint, Prettier, GitHub Actions |
| Node.js | ≥ 24.11.0 (LTS/Krypton) |

---

## Repository Structure

```
/.devcontainer/        VS Code Dev Container (PHP 8.5-fpm-alpine, Xdebug, SQLite)
/.docker/              Docker config files for dev and prod containers
  Dockerfile           Production image (PHP-FPM + Nginx + Supervisor, baked-in code)
  Dockerfile.dev       Dev stack image (PHP-FPM, code mounted as volume)
  Dockerfile.docs      Jekyll + Python docs image (dev stack)
/.github/workflows/    CI/CD pipelines
/api-client/           Auto-generated TypeScript API client — DO NOT EDIT
/app/                  Laravel application (Models, Controllers, Filament, etc.)
/database/             Migrations, factories, seeders
/docs/                 Jekyll documentation site (Ruby, deployed to GitHub Pages)
/public/               Web root (compiled assets in public/build/, SPA in public/cli/)
/resources/js/         Blade frontend assets (NOT the SPA)
/resources/views/      Blade templates
/routes/api.php        REST API routes
/routes/web.php        Web UI routes
/scripts/              Utility scripts (see Scripts section)
/spa/                  Vue 3 SPA Demo (separate npm project)
/tests/                Pest backend tests
```

---

## Docker Setup

### Three containers

| File | Purpose | Runs |
|---|---|---|
| `.docker/Dockerfile.dev` | Local web stack (PHP-FPM, code mounted) | `docker compose up` |
| `.docker/Dockerfile` | Production image (code baked in, nginx+supervisor) | CI build → registry |
| `.devcontainer/Dockerfile` | VS Code Dev Container (PHP 8.5-fpm-alpine, Xdebug, SQLite) | VS Code "Reopen in Container" |
| `.docker/Dockerfile.docs` | Jekyll dev server (Ruby + Python) | `docker compose up docs` |

### Dev stack (`docker-compose.yml`)

```bash
docker compose up         # Start full dev stack (app, web, mysql, valkey, mailpit)
docker compose up docs    # Build and serve docs at http://localhost:4000 (fully automated)
```

Services: `app` (PHP-FPM :8010), `web` (Nginx), `mysql`, `valkey`, `mailpit` (:8026), `docs` (Jekyll :4000).

### Production stack (`docker-compose.prod.yml`)

Uses pre-built image from `ghcr.io/metanull/inventory-app`. Requires a `.env` file in the same directory as the compose file (see `.env.example` for required variables).

```bash
docker compose -f docker-compose.prod.yml up -d
```

> **Status**: Docker deployment to OVH VPS is being prepared but not yet active. The primary deployment path is native PHP-FPM via `scripts/deploy.sh`.

### Dev Container (VS Code)

Open the workspace with "Reopen in Container" (Dev Containers extension). VS Code builds `inventory-app-dev` from `.devcontainer/Dockerfile`. Named volumes for `vendor/` and `node_modules/` are managed automatically.

Rebuild after `.devcontainer/Dockerfile` or `composer.lock` changes:
```bash
docker build -f .devcontainer/Dockerfile -t inventory-app-dev .
```

GitHub Actions runners do **not** use this image — they use `shivammathur/setup-php@v2` directly.

### Documentation container

```bash
make docs-model     # Regenerate model docs via artisan (requires app container running)
make docs-generate  # Regenerate commit + client docs (one-off docs container)
make docs-serve     # Start Jekyll dev server
make docs           # docs-generate + docs-serve
```

---

## Deployment

### OVH VPS (primary — active)

- **Runtime**: native PHP 8.5-FPM + Nginx, no Docker
- **Deploy user**: `deploy@51.75.246.163`, key `~/.ssh/inventory_deploy`
- **App root**: `/opt/inventory/`
- **Layout**: symlinker-style (`releases/`, `shared/`, `current -> releases/<timestamp>`)
- **Shared files**: `/opt/inventory/shared/.env`, `/opt/inventory/shared/storage/`
- **Automated deploy**: GitHub Actions `deploy-ovh.yml` triggers on successful `build.yml` run on `main`
- **Manual deploy**: `scripts/deploy.sh`

The VPS also hosts a second Laravel app (`motivya`) at `/opt/motivya/`. They share MySQL and Valkey — use separate DB indexes (see `.env` for `REDIS_DB` / `REDIS_CACHE_DB` values).

### Build pipeline

`build.yml` on every push to `main` / version tags:

1. PHP 8.5 + Node.js (LTS/Krypton)
2. `composer install --no-dev` + Vite frontend build + SPA build
3. Creates a **curated deployment package** (excludes `tests/`, `scripts/`, `docs/`, `spa/src/`, dev deps)
4. Produces two artifacts:
   - `inventory-app.zip` → GitHub Release → Windows deployment (`deploy.yml`, manual)
   - `release.tar.gz` → Workflow artifact (7-day retention) → OVH deployment (`deploy-ovh.yml`, automatic)

**NEVER** include dev dependencies, test files, scripts, or docs in the deployment package. **NEVER** change artifact naming without updating consumer workflows.

### Docker deployment (in preparation — not yet active)

`build-docker.yml` builds and pushes `ghcr.io/metanull/inventory-app`. `deploy-docker-ovh.yml` deploys via `docker-compose.prod.yml`. Not currently running on the VPS.

---

## Development Commands

### Backend

```powershell
composer dev                      # Start Laravel + queue worker + Vite + SPA concurrently
composer ci-lint                  # Pint + Prettier (auto-fix)
composer ci-lint:test             # Lint check only (non-modifying)
composer ci-test                  # Unit + Api + Web suites (parallel)
composer ci-test:integration      # Integration suite only
composer ci-test:all              # Full test suite
composer ci-build                 # Build frontend assets (npm run build)
composer ci-audit                 # composer validate + audit + npm audit
composer ci-reset                 # Full reset: db + config + install + build + seed
composer ci-before:commit         # Lint dirty files
composer ci-before:push           # OpenAPI doc + full lint
composer ci-before:pull-request   # Full validation
```

**CI matrix suites** (run individually to match GitHub Actions):
```bash
php artisan test --testsuite=Unit          --coverage --parallel --no-ansi --stop-on-failure
php artisan test --testsuite=Api           --coverage --parallel --no-ansi --stop-on-failure
php artisan test --testsuite=Web           --coverage --parallel --no-ansi --stop-on-failure
php artisan test --testsuite=Configuration --coverage --parallel --no-ansi --stop-on-failure
php artisan test --testsuite=Console       --coverage --parallel --no-ansi --stop-on-failure
php artisan test --testsuite=Event         --coverage --parallel --no-ansi --stop-on-failure
php artisan test --testsuite=Integration   --coverage --parallel --no-ansi --stop-on-failure
```

A single `php artisan test` run is not CI parity. Use the suite-level commands above.

### SPA (from `spa/` directory)

```bash
npm test              # Unit tests (excludes integration)
npm run test:all      # All tests including integration
npm run lint          # ESLint auto-fix
npm run lint:check    # ESLint check only
npm run type-check    # vue-tsc --noEmit
npm run quality-check # type-check + lint:check + format:check + test
```

---

## Git Workflow

- Never commit directly to `main`. Use `feature/` or `fix/` branches with PRs.
- Always pull latest `main` before starting work.
- Ensure all lints pass and all tests pass locally before opening a PR.

---

## Laravel Backend Conventions

### Model requirements

Every new model must include: migration, factory, seeder, API resource, controller with Form Request validation, complete test suite.

### Primary keys

- **UUID**: all models via `HasUuids` trait
- **Exceptions**: `Language` and `Country` use ISO code strings (3 characters)
- **User**: integer keys (Laravel auth compatibility)

### Controller patterns

**API controllers** (`app/Http/Controllers/`):
- Use `App\Http\Requests\Api\*` Form Requests
- Return `*Resource` instances
- Support `$request->getIncludeParams()` + `$request->getPaginationParams()` via `HasPaginationAndIncludes`
- Routes use **singular** nouns: `/api/context`, `/api/language`, `/api/item`

**Web controllers** (`app/Http/Controllers/Web/`):
- Use `App\Http\Requests\Web\*` Form Requests
- Return `view()` or `redirect()`
- Routes use **plural** nouns: `/web/contexts`, `/web/languages`, `/web/items`

**Web index pages — request-driven pattern (only approved approach):**

| Piece | Role |
|---|---|
| `App\Http\Requests\Web\Index{Entity}Request` | Extends `IndexListRequest`; declares sort columns, default sort, filters |
| `App\Services\Web\{Entity}IndexQuery` | Encapsulates Eloquent query; receives `ListState`, returns paginator |
| Blade view | Receives paginator + `$listState` — no Eloquent calls inside the view |

Canonical reference: `app/Http/Controllers/Web/ItemController::index()`, `app/Http/Requests/Web/IndexItemRequest`, `app/Services/Web/ItemIndexQuery`, `resources/views/items/index.blade.php`.

❌ Forbidden: `SearchAndPaginate` trait (deleted), Livewire for list filtering/sorting, Eloquent queries in Blade views, `Index*Request` not extending `IndexListRequest`.

### Adding new features

1. Study existing implementation — review similar code first
2. Follow patterns exactly: naming, structure, validation, tests
3. Canonical backend example: `Context` model + `ContextTest.php`
4. Canonical SPA example: `Contexts.vue` + `Contexts.test.ts`
5. Maintain alignment: validation in Controller, Model, Factory, Migration

---

## Image Upload Pipeline — Indirect Upload is Mandatory

User-supplied image binaries are **never** written directly to public storage. All uploads MUST go through the two-stage indirect pipeline. This is a security boundary.

**The canonical flow (do not bypass, parallelize, or reimplement):**

1. Upload creates an `App\Models\ImageUpload` record. Binary stored on **private** `local` disk under `image_uploads/`. NOT web-reachable.
2. Creation dispatches `App\Events\ImageUploadEvent`.
3. `App\Listeners\ImageUploadListener` validates, resizes if needed, creates `App\Models\AvailableImage` (same UUID), deletes `ImageUpload`, dispatches `App\Events\AvailableImageEvent`.
4. `App\Listeners\AvailableImageListener` moves validated file to **public** `public` disk under `images/`.
5. Only then can an `AvailableImage` be attached to an entity. **Attach is a move, not a reference.** Entity image models (`ItemImage`, `CollectionImage`, `PartnerImage`) are NOT pivot tables — they carry their own copy of metadata. Use `*::attachFromAvailableImage()` / `*->detachToAvailableImage()` exclusively.

**Hard rules:**
- ❌ NEVER write uploads to the `public` disk directly.
- ❌ NEVER create an `AvailableImage` from a user upload — only `ImageUploadListener` produces them.
- ❌ NEVER use `FileUpload->disk('public')` in Filament — target `local` + `image_uploads/`.
- ❌ NEVER reimplement the attach/detach move logic — call the model methods.
- ❌ NEVER treat `*Image` models as pivot tables.
- ✅ Tests MUST use `Storage::fake('local')` and `Storage::fake('public')`, dispatch through the real event chain for uploads.

**Image display/download:** Never serve from a constructed `/storage/...` URL. Every surface must register its own `view`/`download` routes, delegating to `App\Http\Responses\Image\InlineImageResponse` / `DownloadImageResponse`. Never call another surface's routes.

---

## Attached-Image Contract and Registry

Every model that stores files under `localstorage.pictures` must:

1. Implement `App\Contracts\StreamableImageFile`.
2. Provide correct implementations of `imageDisk()`, `imageStoragePath()`, `imageMimeType()`, `imageDownloadFilename()`.
3. Be added to `App\Support\Images\AttachedImageRegistry` in the same change.
4. Be covered by `StreamableImageFile` contract tests and registry completeness tests.

`AttachedImageRegistry` is the single source of truth. Storage reporting, orphan cleanup, and audit commands must use the registry — never a local hardcoded list.

---

## Testing Standards

### Backend (Pest)

Test structure under `tests/`:
- `Unit/Models/` — factory tests, model validation
- `Unit/Requests/` — Form Request validation (Web and API)
- `Unit/Services/` — service class tests
- `Api/Resources/` — REST API resource tests (one file per entity)
- `Api/Traits/` — 8 reusable test traits (`TestsApiCrud`, `TestsApiDefaultSelection`, etc.)
- `Filament/{Resources,Pages,Panel,Authorization}/` — Filament UI tests
- `Configuration/`, `Console/`, `Event/`, `Integration/` — cross-cutting

Requirements:
- Use `RefreshDatabase`
- Use factories: `.create()` for DB, `.make()->toArray()` for requests
- Authenticate with Sanctum: `$this->actingAs(User::factory()->create())`
- DRY via traits — see `tests/Api/Resources/ContextTest.php` as canonical example

### Frontend (Vitest, from `spa/`)

- `src/__tests__/feature/` — component behaviour
- `src/__tests__/logic/` — business logic
- Canonical pattern: `Contexts.test.ts`
- Small, focused, deterministic, no shared state between tests

---

## API Client Generation

The TypeScript client is auto-generated from the OpenAPI spec. **Never edit `/api-client/` manually.**

```powershell
.\scripts\generate-api-client.ps1    # Generate from OpenAPI spec
.\scripts\publish-api-client.ps1     # Publish to GitHub Packages (requires PAT)
```

Steps:
1. `composer ci-openapi-doc` → `docs/_openapi/api.json`
2. `openapi-generator-cli` → `/api-client/`
3. Publish as `@metanull/inventory-app-api-client`

The SPA Demo uses the **published npm package**, not the local `/api-client/` directory:
```typescript
import { Configuration, DefaultApi } from '@metanull/inventory-app-api-client';
```

---

## Documentation Site (Jekyll)

Source: `docs/`. Deployed to GitHub Pages via `continuous-deployment_github-pages.yml` on push to `main`.

The CI workflow runs `scripts/generate-commit-docs.py` and `scripts/generate-client-docs.py` directly on the runner before Jekyll build. Do not break these scripts.

**Local development** (via Docker):
```bash
docker compose up docs    # builds complete docs and starts Jekyll at http://localhost:4000
```

`docker compose up docs` is all that is needed. The compose dependency chain handles everything automatically:
1. `docs-model` (init container) — runs `php artisan docs:models` against the live MySQL instance, then exits.
2. `docs` — starts once `docs-model` has completed successfully; its entrypoint (`.docker/docs-entrypoint.sh`) runs `generate-commit-docs.py` and `generate-client-docs.py` inside the container, then starts Jekyll.

No host-side PHP, Python, or tooling required.

**Front matter** — all Markdown files must include:
```yaml
---
layout: default
title: Page Title
nav_order: 1
---
```

**Link conventions:**
- Directory links: include trailing slash — `[API Models](api-models/)`
- File links: omit extension — `[Guidelines](guidelines)`
- Absolute links: start with `/`, omit `/docs/` prefix

---

## Security Practices

- No hardcoded secrets — use environment variables
- Use Laravel abstractions (Storage, Config, etc.)
- Always validate with Form Requests
- Eloquent only — no raw SQL
- Sanctum tokens for API, standard Laravel auth for web
- Input sanitization at system boundaries only

---

## Common Pitfalls

- ❌ Editing files in `/api-client/` (auto-generated)
- ❌ Using `fetch`, `axios`, or local `/api-client/` in SPA Demo — use the published npm package
- ❌ Altering existing migrations — create new ones
- ❌ Using `any` type in TypeScript
- ❌ Direct filesystem access in PHP — use Laravel Storage
- ❌ Committing directly to `main`
- ❌ Inline SVG or custom icon components — use Heroicons only
- ❌ Creating components without tests
- ❌ Reintroducing `SearchAndPaginate` (deleted) — use `IndexListRequest` + `{Entity}IndexQuery`
- ❌ Livewire for list filtering/sorting/searching/pagination on web index pages
- ❌ Eloquent queries in Blade list, detail, or form views
- ❌ Creating an `Index*Request` web class that does not extend `IndexListRequest`
- ❌ Uploading user images directly to `public` disk — must use `ImageUpload` → event chain
- ❌ Creating `AvailableImage` from a user upload — only `ImageUploadListener` produces them
- ❌ `FileUpload->disk('public')` in Filament for user image uploads
- ❌ Piping test/build commands through output filters — always run unpiped
- ❌ Treating `*Image` models as pivot tables
- ❌ Building image URLs from `Storage::url()` or `/storage/...` paths — use named routes
- ❌ Running `php artisan migrate:fresh` or `migrate:refresh` on the OVH VPS

---

## Additional Resources

- **Live Docs**: https://metanull.github.io/inventory-app/
- **API Docs**: http://localhost:8010/docs/api (dev stack running)
- **GitHub Issues**: milestone tracking and EPICs
