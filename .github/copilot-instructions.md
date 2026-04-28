Never generate or modify code unless explicitly instructed.
Never generate or modify documentation and operation summaries unless explicitly instructed.
If there is a doubt, ambiguity, or lack of clarity about the requirements, always ask the user for clarification before proceeding.
If there are multiple options or approaches to implement a feature, ALWAYS ask the user to choose the preferred one before proceeding.
If there are multiple options or approaches to implement a feature, ALWAYS present first the option that best aligns with established project patterns, conventions and frameworks.
Always verify if the requested feature or functionality already exists in the codebase before implementing new code.
Always verify if the requested feature or functionality is compliant with project patterns, conventions and framework best practices.
If the requested feature or functionality does not align with established project patterns, conventions and frameworks, ALWAYS explain the proper alternative and ask the user for confirmation before proceeding.
Never assume or guess user intentions; always ask for explicit confirmation when in doubt.
Never introduce new libraries, dependencies, or frameworks without explicit user approval.
Never duplicate existing code; always reuse or refactor.
Only add comment in code to explain the non obvious business logic.
Never write comments in code to explain what the request was about.
Always follow established project patterns and conventions exactly.
Never alter code to fit testing needs; tests must adapt to existing code.
Never write test that depend on external data or state; tests must be self-contained and isolated.
Never alter existing database migrations; create new migrations for changes.
Never use the Terminal to read or write files. Always use VS Code file explorer and editor features for file operations.
Never create scripts to modify files. Use VS Code refactoring and multi-file editing features instead.
Never use linux commands and escaping in Terminal on Windows. Always use native Windows commands in PowerShell.
Always write database-agnostic migrations and code. Production uses MariaDB, development uses SQLite, other Laravel supported engines are possible.
If you cannot complete a task according to these rules, you MUST stop and report the exact point of failure. Do not attempt any workarounds
If you modify existing code, never keep the original code as a fallback or comment it out — remove it entirely.
NEVER implement fallback mechanisms without explicit approval
Code must fail fast, and never fallback to degraded functionality
Functions and classes must respect the single-responsibility principle
Code must respect DRY and KISS principles — avoid duplication and unnecessary complexity
Code must be testable, and you must write tests for all new features and bug fixes. Do not change original code to make it easier to test.
Test must cover the business logic only — do not test the framework or third-party libraries internal logic.
Use mocking/stubbing to isolate code under test.


---

## Active milestone: Filament 3 is the main UI

> This project is mid-migration under **Milestone 3** (see `temp_MILESTONE3_FILAMENT_MIGRATION.md` and EPIC 0 — GitHub issue #849). The rules below supersede any Blade/Livewire back-office guidance in this file or the per-file instruction files until EPIC 14 rewrites them.

1. **`/admin` (Filament 3) is the main UI**, not a restricted admin-only back-office. Every authenticated role except `Non-verified users` must be able to reach `/admin`.
2. **Three-tier authorization model** (enforced by EPIC 2):
   - **Tier 1 — Panel gate**: Spatie permission `access-admin-panel` grants entry to `/admin`. Assigned to `Visitor`, `Regular User`, and `Manager of Users`. Never assigned to `Non-verified users`.
   - **Tier 2 — Navigation / Resource visibility**: per-feature Spatie permissions (`view-data`, `manage-users`, `manage-roles`, `manage-settings`, `manage-reference-data`) drive `canViewAny()` and `shouldRegisterNavigation()`.
   - **Tier 3 — Record / Action authorization**: existing `App\Policies\*` classes, unchanged.
3. **Test placement during Milestone 3**:
   - All new Filament tests live under `tests/Filament/{Resources,Pages,Panel,Authorization}/`.
   - Never add tests to `tests/Web/` — that suite is frozen and deleted in EPIC 12. The Blade/Livewire back-office no longer receives new coverage.
   - `tests/Api/`, `tests/Unit/`, `tests/Configuration/`, `tests/Console/`, `tests/Event/`, `tests/Integration/` remain the correct homes for non-UI tests.
4. **Self-service is first-class**: user profile, password change, two-factor enrolment, browser-session logout, and account deletion are delivered as a Filament `ProfilePage` (EPIC 10b). Do not reintroduce Jetstream Blade profile pages.
5. **Forward-pointer to EPIC 14**: existing sections of this file (and `php.instructions.md`, `test-php.instructions.md`) that describe `IndexListRequest`, `{Entity}IndexQuery`, `SearchableSelect`, `SearchAndPaginate`, Livewire list components, or `/web/*` routes describe the **legacy** stack being removed. Do not author new code against those patterns. When in doubt, prefer a Filament Resource / Relation Manager / Page.

---

## Strict `/admin` ⇄ `/web` auth isolation (hard requirement)

`/admin` (Filament) and `/web` (Blade/Jetstream/Livewire) authentication flows MUST stay strictly isolated. `/web` is scheduled for removal in EPIC 12 / EPIC 14; until then it lives side-by-side with `/admin` but never shares control flow.

1. **`/admin` is fully Filament-native** for login, MFA challenge, and MFA setup. All three are Filament `SimplePage` Livewire components served from the `admin` panel routes. Post-login, post-challenge, and post-setup redirects resolve **only** to `admin` panel URLs (`Filament::getCurrentPanel()->route(...)` / `Filament::getUrl()`). The MFA challenge transition is a Livewire-aware `$this->redirect(...)` from the Filament login page itself — never a Fortify response contract.
2. **Fortify remains the underlying auth/MFA service layer**, used as a service, not as an orchestrator:
   - ✅ Allowed in `/admin`: `Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider`, `Laravel\Fortify\Actions\EnableTwoFactorAuthentication`, `Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication`, `Laravel\Fortify\Actions\DisableTwoFactorAuthentication`, `Laravel\Fortify\TwoFactorAuthenticatable` (model trait), `Fortify::currentEncrypter()`, and `Features::twoFactorAuthentication()` checks.
   - ❌ Forbidden in `/admin`: `Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable`, `Laravel\Fortify\Actions\AttemptToAuthenticate`, `Laravel\Fortify\Actions\PrepareAuthenticatedSession`, `Laravel\Fortify\Actions\EnsureLoginIsNotThrottled`, `Laravel\Fortify\Actions\CanonicalizeUsername`, `Laravel\Fortify\Contracts\LoginResponse`, `Laravel\Fortify\Contracts\TwoFactorLoginResponse`, `Laravel\Fortify\Contracts\TwoFactorChallengeViewResponse`, and the Fortify routes `two-factor.login` / `two-factor.login.store`.
3. **No shared session marker bridges the two flows.** Specifically: do not use `filament.auth.panel`, do not use Fortify’s `login.id` from `/admin`, do not introduce any equivalent cross-surface marker. `/admin` uses panel-namespaced session keys only (e.g. `filament.admin.2fa.user_id`).
4. **No code in `app/Filament/**`, `app/Http/Controllers/Filament/**`, `app/Http/Middleware/Filament/**`, or `app/Providers/Filament/**` may reference**: `two-factor.login`, `filament.auth.panel`, `login.id`, `RedirectsIfTwoFactorAuthenticatable`, `AttemptToAuthenticate`, `PrepareAuthenticatedSession`, `Illuminate\Routing\Pipeline`, `TwoFactorChallengeViewResponse`, `TwoFactorLoginResponse`, or any Blade view under `resources/views/auth/`.
5. **`/web` Fortify flow remains untouched** until EPIC 14 retires `/web` entirely. `/web` continues to use Fortify’s default `route('two-factor.login')` + `view('auth.two-factor-challenge')`. Do not customize either of those for `/admin` purposes.
6. **Tests for the boundary live under `tests/Filament/`** (Authorization or Pages). They MUST include both directional regressions: an `/admin` flow that completes without ever resolving any `/web` route, and a `/web` flow that completes without ever resolving any `/admin` route — even when the same browser session previously interacted with the other surface.

When you remove `/web` later (EPIC 14), the `/admin` flow delivered under this rule must remain unchanged — that is the point of strict isolation.

---

## Project Overview

The **Inventory Management API** is a comprehensive Laravel 12 backend application for museum inventory management at Museum With No Frontiers. This is a monorepo containing:

- **Backend Application**: Laravel 12 (PHP 8.2+) providing both REST API and server-rendered frontend (the main UI)
- **Documentation Site**: Jekyll-based static site in `/docs/`
- **TypeScript API Client**: Auto-generated from OpenAPI spec and published to npm (source in `/api-client/`)
- **SPA Demo**: Vue 3 + TypeScript example application demonstrating API client usage

### Key Technologies

- **Backend**: Laravel 12, PHP 8.2+ (CI uses 8.4), Eloquent ORM, Sanctum, Blade templates, Livewire
- **Frontend (Web UI)**: Blade templates, Livewire, Alpine.js, Tailwind CSS 4, Vite
- **Frontend (SPA Demo)**: Vue 3, TypeScript, Vite, Pinia, Vue Router, Tailwind CSS 4
- **Database**: SQLite (development), MariaDB (production) with UUID primary keys
- **Testing**: Pest (backend, extends PHPUnit), Vitest (frontend)
- **Tooling**: Composer, npm, Laravel Pint, ESLint, Prettier, GitHub Actions
- **Node.js**: Requires ≥24.11.0 (LTS/Krypton)

## Architecture & Structure

### Directory Layout

```
/.github/instructions/ # File-specific coding guidelines
/.legacy-database/     # Git submodules containing SQL dumps and DDL of the legacy databases we are replacing with our new system
/api-client/           # Auto-generated TypeScript API client (DO NOT EDIT)
/app/                  # Laravel application code (Models, Controllers, etc.)
/database/             # Migrations, factories, seeders
/docs/                 # Jekyll documentation site (Ruby-based)
/resources/js/         # Blade frontend assets (app.js only — NOT the SPA)
/resources/views/      # Blade templates for server-rendered frontend
/routes/api.php        # REST API route definitions
/routes/web.php        # Web UI route definitions
/scripts/              # Utility scripts (e.g., API client generation)
  /importer/           # Data importer (import and convert data legacy databases)
/spa/                  # Vue 3 SPA Demo application (separate npm project)
  /src/components/     # Reusable Vue components
  /src/stores/         # Pinia state management stores
  /src/views/          # Page-level Vue components
  /src/__tests__/      # Vitest test suites
/tests/                # Pest backend tests
  /README.md           # Testing guidelines and directory structure
```

### Application Components

1. **Backend Application** - Main Laravel application with two interfaces:
   
   **REST API** (`/api` routes)
   - RESTful endpoints for programmatic access
   - Sanctum authentication for all endpoints
   - Resource controllers with Form Request validation
   - OpenAPI/Swagger documentation at `/api/docs`
   
   **Server-Rendered Frontend** (`/web` routes) - **Main UI**
   - Server-rendered Blade templates with Livewire
   - Direct Laravel model/controller interactions (NOT via API)
   - Alpine.js and Tailwind CSS
   - Primary interface for end users

2. **Documentation Site** (`/docs/`)
   - Jekyll-based static site deployed to GitHub Pages
   - Auto-generated API documentation and commit history

3. **SPA Demo** (`/cli` routes) - Example application only
   - Vue 3 + TypeScript demonstrating API client usage
   - Source code in `/spa/src/`
   - Uses the **published npm package** `@metanull/inventory-app-api-client` from GitHub Packages
   - Does NOT use the local `/api-client/` directory directly
   - Serves as reference implementation for external API consumers

4. **TypeScript API Client** (`/api-client/`)
   - Auto-generated from OpenAPI spec using `openapi-generator-cli`
   - Published to GitHub Packages as `@metanull/inventory-app-api-client`
   - **DO NOT EDIT** - source of truth is the OpenAPI spec in `/docs/_openapi/api.json`

5. **Data Importer** (`/scripts/importer/`)
   - PowerShell and TypeScript scripts to import data from legacy databases
   - Modular importers for different data phases
   - Uses environment variables for configuration

## Development Standards

### Code Quality Requirements

- **All code must pass linting** without warning: Laravel Pint (PHP), ESLint (JS/TS)
- **All tests must pass**: No failing tests allowed in PRs
- **No TypeScript errors or warnings**: Must pass `npx tsc --noEmit`
- **No unused variables or imports**: Enforced by linters
- **Explicit typing**: Never use `any` type in TypeScript

### Build & Test Commands

**Development server** (starts Laravel, queue worker, Vite, and SPA concurrently):
```powershell
composer dev
```

**Backend commands:**
```powershell
composer ci-lint          # Run Pint + Prettier (auto-fix)
composer ci-lint:test     # Lint check only (non-modifying)
composer ci-test          # Run Unit, Api, and Web suites with Laravel parallel runner
composer ci-test:integration  # Run Integration suite only
composer ci-test:all      # Run the full test suite in one command
composer ci-build         # Build frontend assets (npm run build)
composer ci-audit         # Composer validate + audit + npm audit
composer ci-reset         # Full reset: db + config + install + build + seed
```

**Backend CI matrix parity**
```powershell
php artisan test --testsuite=Unit --coverage --parallel --no-ansi --stop-on-failure
php artisan test --testsuite=Api --coverage --parallel --no-ansi --stop-on-failure
php artisan test --testsuite=Web --coverage --parallel --no-ansi --stop-on-failure
php artisan test --testsuite=Configuration --coverage --parallel --no-ansi --stop-on-failure
php artisan test --testsuite=Console --coverage --parallel --no-ansi --stop-on-failure
php artisan test --testsuite=Event --coverage --parallel --no-ansi --stop-on-failure
php artisan test --testsuite=Integration --coverage --parallel --no-ansi --stop-on-failure
```

Use the CI matrix commands above, or equivalent VS Code tasks, when validating backend changes that must match GitHub Actions behavior. Do not treat a single plain `php artisan test` run as CI parity.

> **Windows dev machine — use the Dev Container (PHP 8.4 + Node.js 24).**
> The local PHP installation is 8.2 and lacks several extensions required by the current codebase (Filament 3 needs `intl`, `zip`, `gd`, `exif`). Open the workspace with **"Reopen in Container"** (Dev Containers extension). VS Code builds `inventory-app-dev` from `.devcontainer/Dockerfile` and all tooling runs natively inside the container — no `docker run` wrappers needed:
> ```bash
> php artisan test --testsuite=Api --coverage --parallel --no-ansi --stop-on-failure
> vendor/bin/pint --no-ansi
> ```
> Rebuild the image after `.devcontainer/Dockerfile` or `composer.lock` changes: `docker build -f .devcontainer/Dockerfile -t inventory-app-dev .` (run from the Windows host, then reopen in container). GitHub Actions runners (Linux) do **not** use this image — they use `shivammathur/setup-php@v2` directly.

VS Code task runs are terminal-based and do not feed results back into the Testing panel. For interactive PHP test discovery and per-test results inside VS Code, use the `PHPUnit & Pest Test Explorer` extension and run tests from the Testing view instead of from tasks.

**SPA commands** (run from `/spa/` directory):
```bash
npm test                  # Run unit tests (excludes integration)
npm run test:all          # Run all tests including integration
npm run lint              # ESLint auto-fix
npm run lint:check        # ESLint check only
npm run type-check        # vue-tsc --noEmit
npm run quality-check     # type-check + lint:check + format:check + test
```

### Git Workflow

- **Never commit directly to `main`**: Use feature branches with PRs
- **Branch naming**: Use `feature/` or `fix/` prefix
- **Before starting**: Always pull latest `main` to work on current code
- **Before PR**: Ensure all lints pass and all tests pass locally
- **Pre-commit**: `composer ci-before:commit` (lint dirty files)
- **Pre-push**: `composer ci-before:push` (OpenAPI doc + full lint)
- **Pre-PR**: `composer ci-before:pull-request` (full validation)

## Laravel Backend Conventions

### Model Requirements

Every new model must include:
- Migration (never alter existing migrations, create new ones)
- Factory with comprehensive test coverage
- Seeder for development data
- Resource for API output formatting
- Controller with Form Request validation
- Complete test suite (see Testing section)

### Primary Keys

- **UUID**: Used for all models (via `HasUuids` trait)
- **Exceptions**: `Language` and `Country` use ISO code strings (3 characters)
- **User model**: Uses Laravel default integer keys for auth compatibility

### Controller Patterns

**API controllers** (in `app/Http/Controllers/`):
- Use `App\Http\Requests\Api\*` Form Requests
- Return `*Resource` instances
- Support `$request->getIncludeParams()` and `$request->getPaginationParams()` via `HasPaginationAndIncludes` concern
- API routes use **singular** nouns: `/api/context`, `/api/language`, `/api/item`

**Web controllers** (in `app/Http/Controllers/Web/`):
- Use `App\Http\Requests\Web\*` Form Requests
- Return `view()` or `redirect()` responses
- Apply middleware in constructor: `$this->middleware(['auth', 'permission:...'])`
- Web routes use **plural** nouns: `/web/contexts`, `/web/languages`, `/web/items`

#### Web list pages — request-driven pattern (the only approved approach)

Every web index (`index()`) action **must** follow the request-driven list pattern:

| Piece | Role |
|---|---|
| `App\Http\Requests\Web\Index{Entity}Request` | Extends `IndexListRequest`; declares allowed sort columns, default sort, and allowed filters. |
| `App\Services\Web\{Entity}IndexQuery` | Encapsulates the Eloquent query; receives a `ListState` and returns a paginator. |
| `App\Support\Web\Lists\ListDefinition` | (base class) Wired via `IndexListRequest` — provides `listState()` to the controller. |
| Blade view | Receives the paginator and `$listState` — **no Eloquent calls inside the view**. |

Canonical reference implementation:
- Controller: `app/Http/Controllers/Web/ItemController::index()`
- Request: `app/Http/Requests/Web/IndexItemRequest`
- Query service: `app/Services/Web/ItemIndexQuery`
- Blade view: `resources/views/items/index.blade.php`

**Forbidden patterns — never reintroduce:**
- ❌ `App\Support\Web\SearchAndPaginate` trait (deleted — use the request-driven pattern)
- ❌ Mounting a Livewire component to handle list filtering, sorting, searching, or pagination on a web list page
- ❌ Issuing Eloquent queries directly from any Blade list view, detail view, or form view
- ❌ Creating an `Index*Request` class for a web list page that does **not** extend `IndexListRequest`

All controllers follow standard resource methods:
- `index()` - List all records
- `show($id)` - Show single record
- `store(Request)` - Create new record
- `update(Request, $id)` - Update existing record
- `destroy($id)` - Delete record

Additional endpoints for models with scopes:
- Example: `Language::default()` scope → `GET /api/language/default`

### Validation & Resources

- Use Form Request classes for input validation
- Use Resource classes for output formatting
- Keep validation rules aligned with Model, Factory, and Migration constraints
- Never use vendor-specific code when Laravel offers built-in features

## Vue.js Frontend Conventions (SPA Demo Only)

**Note**: These conventions apply to the SPA Demo in `/spa/src/` - a reference implementation for external API consumers.

### Component Structure

- **Always use**: `<script setup lang="ts">` for all SFCs
- **Composition API only**: Use `ref`, `computed`, `watch`, etc.
- **Type everything**: Props, emits, state - never use `any`
- **Scoped styles**: `<style scoped>` for component-specific CSS

### State Management

- **Pinia stores**: For ALL shared state (never use component `data` or `reactive`)
- **Store patterns**: Follow existing patterns in `/spa/src/stores/`
- **API calls**: Use the **published npm package** `@metanull/inventory-app-api-client` - NEVER use `fetch`, `axios`, or the local `/api-client/` directory directly

### Reusable Components

Extract repeated UI patterns into shared components. Key component categories:

- **Layout**: ListView, DetailView, AppHeader, AppFooter
- **Cards**: Card, NavigationCard, StatusCard, InformationCard
- **Forms**: FormInput, GenericDropdown, Toggle
- **Display**: DisplayText, InternalName, DateDisplay, Title, Uuid
- **Tables**: TableElement, TableHeader, TableRow, TableCell

See `/spa/src/components/` for complete component library.

### Styling

- **Tailwind CSS**: Primary styling method
- **Responsive design**: Use Tailwind breakpoints (`sm:`, `md:`, `lg:`, `xl:`)
- **Entity colors**: Consistent across navigation, pages, and components (defined in `config/app_entities.php`)
  - Items: `teal`, Partners: `yellow`, Languages: `fuchsia`
  - Countries: `indigo`, Contexts: `indigo`, Projects: `teal`, Collections: `yellow`

### Icons

- **Heroicons ONLY**: Import from `@heroicons/vue/24/solid` or `/24/outline`
- **NO inline SVG**: Never create custom icon components
- **Semantic aliases**: Use descriptive names (e.g., `import { CogIcon as ContextIcon }`)

## Testing Standards

### Backend Tests (Pest)

Tests use [Pest](https://pestphp.com/) (which extends PHPUnit). Test organization in `/tests/`:
- `/Unit/Models/` - Factory tests, model validation
- `/Unit/Requests/` - Form Request validation tests (both Web and API)
- `/Unit/Enums/` - Enum tests
- `/Unit/Services/` - Service class tests
- `/Api/Resources/` - REST API resource tests (one file per entity)
- `/Api/Middleware/` - Authentication and permission tests
- `/Api/Traits/` - 8 reusable test traits (`TestsApiCrud`, `TestsApiDefaultSelection`, etc.)
- `/Web/Pages/` - Server-rendered page tests
- `/Web/Livewire/` - Livewire component tests
- `/Configuration/` - App configuration tests
- `/Console/` - Artisan command tests
- `/Event/` - Event and listener tests
- `/Integration/` - Cross-cutting integration tests

Test requirements:
- DRY principle — API resource tests compose behavior via traits (e.g., `TestsApiCrud`, `TestsApiDefaultSelection`)
- Follow existing patterns exactly when adding new tests — they must respect the existing directory structure
- Only test custom business logic — don't test the framework
- Use `RefreshDatabase` trait
- Use factories for test data (`.create()` for DB, `.make()->toArray()` for requests)
- Authenticate with Sanctum: `$this->actingAs(User::factory()->create())`

Canonical examples:
- API resource test: `tests/Api/Resources/ContextTest.php` (composes traits, minimal boilerplate)
- Related entity tests: `tests/Api/Resources/ItemTranslationTest.php`

### Frontend Tests (Vitest)

Test organization in `/spa/src/__tests__/`:
- `/feature/` - Component functionality and behavior
- `/logic/` - Business logic and computations

Test requirements:
- **Small and focused**: Single objective per test
- **Deterministic**: No randomness or time-based dependencies
- **Independent**: No reliance on other test state
- **Explicit mocking**: All external dependencies faked/mocked per-test
- **Reference example**: Use `Contexts.test.ts` as canonical pattern

## API Client Generation & Publishing

The TypeScript client is auto-generated and published to npm. The local `/api-client/` directory contains the generated source - **NEVER edit manually**.

### Generation & Publishing Process

```powershell
# Generate client from OpenAPI spec
.\scripts\generate-api-client.ps1

# Publish to GitHub Packages (requires PAT)
.\scripts\publish-api-client.ps1 -Credential (Get-Credential)
```

Steps performed:
1. Generate OpenAPI spec: `composer ci-openapi-doc` → `docs/_openapi/api.json`
2. Generate TypeScript client: `openapi-generator-cli` → `/api-client/`
3. Publish to GitHub Packages as `@metanull/inventory-app-api-client`

### Using the Published Client

**The SPA Demo uses the published npm package** (installed in `/spa/node_modules/`), not the local `/api-client/` directory:

```typescript
import { Configuration, DefaultApi } from '@metanull/inventory-app-api-client';

const api = new DefaultApi(new Configuration({ basePath: 'https://api.url' }));
api.addressIndex().then(response => console.log(response.data));
```

## Documentation Site (Jekyll)

The `/docs/` directory contains a Jekyll-based documentation site deployed to GitHub Pages.

### Key Files

- `_config.yml` - Main Jekyll configuration
- `index.md` - Homepage (requires front matter)
- Auto-generated content maintained by CI/CD (e.g., commit history)

### Front Matter Requirements

All Markdown files must include a front matter block like this:
```yaml
---
layout: default
title: Page Title
nav_order: 1
---
```

Optional fields: `description`, `permalink`, `parent`, `has_children`

### Link Conventions

- **Directory links**: Include trailing slash - `[API Models](api-models/)`
- **File links**: Omit extension - `[Guidelines](guidelines)`
- **Absolute links**: Start with `/`, omit `/docs/` prefix - `[Guidelines](/guidelines)`
- **Non-markdown files**: Create placeholder with `permalink` and `layout: null`

### Local Testing

Use WSL for Ruby/Jekyll commands:
```bash
wsl bash -c 'cd /path/to/project/docs && bundle exec jekyll serve'
```

## Code Consistency Patterns

### Adding New Features

When adding new models, controllers, or components:

1. **Study existing implementation**: Review similar existing code first
2. **Follow patterns exactly**: Naming, structure, validation, tests
3. **Use canonical examples**:
   - Backend: `Context` model, controller, tests in `/tests/Api/Resources/ContextTest.php`
   - Frontend: `Contexts.vue` page in `/spa/src/views/` and tests in `/spa/src/__tests__/`
4. **Maintain alignment**: Validation in Controller, Model, Factory, Migration

### DRY Principle

- Extract common logic into helpers or traits
- Reuse components instead of duplicating code
- Follow established patterns for similar functionality

## Security & Best Practices

- **No hardcoded secrets**: Use environment variables
- **Laravel abstractions**: Use framework features (Storage, Config, etc.)
- **Input validation**: Always validate with Form Requests
- **SQL injection prevention**: Use Eloquent exclusively
- **Authentication**: Sanctum tokens for API, standard Laravel auth for web

## Image upload pipeline — indirect upload is mandatory

User-supplied image binaries are **never** written directly to public storage. All image uploads — whether issued from the API, the legacy `/web` UI, or the Filament `/admin` UI — MUST go through the existing two-stage indirect pipeline. This is a security boundary, not a stylistic preference.

**The canonical flow (do not bypass, do not parallelize, do not reimplement):**

1. Upload writes a new `App\Models\ImageUpload` record. The binary is stored on the **private** `local` disk under the `image_uploads` directory (see `config/localstorage.php`). It is NOT web-reachable.
2. Creation dispatches `App\Events\ImageUploadEvent`.
3. `App\Listeners\ImageUploadListener` validates the binary (Intervention Image), resizes if it exceeds the configured max dimensions, creates an `App\Models\AvailableImage` record (same UUID), deletes the `ImageUpload`, and dispatches `App\Events\AvailableImageEvent`.
4. `App\Listeners\AvailableImageListener` moves the validated file to the **public** `public` disk under the `images` directory.
5. Only then can an `AvailableImage` be attached to a host entity (Item, Collection, Partner, …). **Attach is a move, not a reference.** The per-entity image models (`ItemImage`, `CollectionImage`, `PartnerImage`) are **NOT pivot tables** — they do not hold a foreign key to `available_images`. Each row is a standalone, entity-owned record that carries its own copy of the metadata (`path`, `original_name`, `mime_type`, `size`, `alt_text`, `display_order`). The canonical transition is implemented by the model methods `ItemImage::attachFromAvailableImage()`, `CollectionImage::attachFromAvailableImage()`, `PartnerImage::attachFromAvailableImage()` (and their reverse `detachToAvailableImage()` counterparts).

**How attach actually works (in a DB transaction):**

- Moves the file from `public`/`images/` to `public`/`pictures/` (directories from `config/localstorage.php`).
- Creates the `ItemImage` / `CollectionImage` / `PartnerImage` row, **reusing the same UUID** as the source `AvailableImage`.
- **Deletes** the `AvailableImage` record.

**How detach works (exact reverse, in a DB transaction):**

- Moves the file from `public`/`pictures/` back to `public`/`images/`.
- Recreates the `AvailableImage` row with the same UUID.
- Deletes the entity image row.

**Consequences that all code MUST honor:**

- Each image is **unique and owned by at most one entity at a time**. An `AvailableImage` is transient — it exists only while the image is unattached.
- The `AvailableImage` pool stays small and manageable (the production corpus is ~25 000 images; holding every attached image in the pool would make the pool unusable).
- Attach errors can be corrected by detach → re-attach **without re-uploading the binary**.
- A Filament/API/Web "attach" code path MUST call the existing `*::attachFromAvailableImage()` model method. Do not reimplement the move, do not create an `ItemImage`/`CollectionImage`/`PartnerImage` row directly from an `AvailableImage` without going through it.
- A "detach" code path MUST call the existing `*->detachToAvailableImage()` model method. Do not reimplement the reverse move.

**Rules — apply to every UI surface (API, `/web`, `/admin` Filament, future surfaces):**

- ❌ **NEVER** write user-uploaded image binaries to the `public` disk, the `images` directory, or any web-reachable location directly. The `public` disk is the **output** of validation, never an input.
- ❌ **NEVER** create an `AvailableImage` record from a user upload. `AvailableImage` is produced exclusively by `ImageUploadListener` or by a detach operation.
- ❌ **NEVER** use `Filament\Forms\Components\FileUpload->disk('public')` (or any equivalent) to accept a user image. Filament image upload fields MUST target the `local` disk + `image_uploads` directory and persist as an `ImageUpload` record so the existing event chain runs.
- ❌ **NEVER** reimplement validation, resizing, or thumbnailing in a new controller, action, page, or Filament component. Trigger the existing event chain.
- ❌ **NEVER** reimplement the attach or detach file-move logic. Call `*::attachFromAvailableImage()` / `*->detachToAvailableImage()`.
- ❌ **NEVER** treat `ItemImage`/`CollectionImage`/`PartnerImage` as pivot tables; they have no foreign key to `available_images` and the same image cannot be simultaneously attached to multiple entities.
- ❌ **NEVER** introduce a new disk, directory, or storage path for image uploads or attachments.
- ✅ A Filament "upload" Action / Page / Resource MUST create an `ImageUpload` (so `ImageUploadEvent` fires) and surface the resulting `AvailableImage` once the listeners have run.
- ✅ A Filament "attach image to entity" Action MUST pick from the existing `AvailableImage` pool via server-side search and invoke `*::attachFromAvailableImage()`; it MUST NOT accept a raw file.
- ✅ A Filament "detach image" Action MUST invoke `*->detachToAvailableImage()` so the binary returns to the `AvailableImage` pool under the same UUID.
- ✅ Tests covering upload, attach, or detach flows MUST use `Storage::fake('local')` and `Storage::fake('public')`, dispatch through the real event chain (or assert it was dispatched) for uploads, and never write directly to the `public` disk outside the existing listener/model methods.

**Image display and download — each UI surface MUST own its own routes, all backed by the shared `Responsable` classes:**

Image bytes are NEVER served from a constructed `/storage/...` URL or any direct disk path. Every surface (`/api`, `/web`, `/admin` Filament, future surfaces) MUST register its OWN distinct `view` (inline) and `download` (attachment) routes per image model — surfaces NEVER call into another surface's routes. Controllers are one-line delegations to two shared `Responsable` classes that wrap `App\Http\Responses\FileResponse`:

- `App\Http\Responses\Image\InlineImageResponse` — for inline display.
- `App\Http\Responses\Image\DownloadImageResponse` — for `Content-Disposition: attachment` downloads.

Both are constructed from any model implementing `App\Contracts\StreamableImageFile` (currently `AvailableImage`, `ItemImage`, `CollectionImage`, `PartnerImage`). The four model methods (`imageDisk()`, `imageStoragePath()`, `imageMimeType()`, `imageDownloadFilename()`) own all per-model variation (config key, mime fallback, download filename rule); response classes and controllers contain zero duplication.

**Existing surfaces (do not touch their routes; tests pin them):**
- `AvailableImage` — `App\Http\Controllers\AvailableImageController@view|download` (API) and `App\Http\Controllers\Web\AvailableImageController@view|download` (Web).
- `ItemImage` — `App\Http\Controllers\ItemImageController@view|download` (API) and `App\Http\Controllers\Web\ItemImageController@view|download` (Web).
- `CollectionImage` — `App\Http\Controllers\CollectionImageController@view|download` (API) and `App\Http\Controllers\Web\CollectionImageController@view|download` (Web).
- `PartnerImage` — `App\Http\Controllers\PartnerImageController@view|download` (API) and `App\Http\Controllers\Web\PartnerImageController@view|download` (Web).

**Mandatory controller body shape — every `view`/`download` action on every surface:**

```php
public function view(ItemImage $itemImage)
{
    return new InlineImageResponse($itemImage);
}

public function download(ItemImage $itemImage)
{
    return new DownloadImageResponse($itemImage);
}
```

Optional guards (parent-ownership checks, policy gates, signed-URL validation, …) MAY appear before the `return`, but the action MUST end with `return new InlineImageResponse(...)` or `return new DownloadImageResponse(...)`. Example:

```php
public function view(Item $item, ItemImage $itemImage)
{
    if ($itemImage->item_id !== $item->id) {
        abort(404);
    }

    return new InlineImageResponse($itemImage);
}
```

**Rules:**

- ❌ **NEVER** build an image URL from `Storage::url()`, `asset('storage/images/...')`, `/storage/pictures/...`, or any direct disk path. There is no `getUrl()` accessor on `AvailableImage`; URL conventions on the public disk are an implementation detail of the response classes, not an API for clients.
- ❌ **NEVER** call `FileResponse::view()` / `FileResponse::download()` directly from a controller, action, page, or Filament component. Always go through `InlineImageResponse` / `DownloadImageResponse`.
- ❌ **NEVER** duplicate the disk + directory + path + filename + mime-type resolution in a controller body. That logic lives exclusively in the model's `StreamableImageFile` methods and the two response classes.
- ❌ **NEVER** route a Filament `/admin` page to a `/api/...` or `/web/...` image URL. Filament must own its own `/admin/...` view/download routes (registered in the Filament panel) returning the same response classes.
- ❌ **NEVER** add a third low-level streaming primitive. The two response classes are the canonical boundary; extend them instead of creating siblings.
- ✅ Each surface registers its OWN distinct named routes per model (e.g. `item-image.view` is API; the Web equivalent is `items.item-images.view`; the Filament equivalent is a separate name like `filament.admin.item-image.view`). Resolve URLs only from routes belonging to the surface that is rendering them.
- ✅ Filament view/edit pages, gallery components, and lightboxes MUST resolve URLs from Filament's own named routes (which themselves return `InlineImageResponse` / `DownloadImageResponse`). Same for the `AvailableImage` pool.
- ✅ Tests asserting display/download MUST hit a route on the surface under test (not a route from another surface) and assert the streamed response, not a constructed disk URL.

## File-Specific Instructions

For detailed language and framework-specific guidelines, see:

- `php.instructions.md` - PHP/Laravel standards
- `ts.instructions.md` - TypeScript standards
- `vue.instructions.md` - Vue.js component patterns
- `test-php.instructions.md` - Backend testing guidelines
- `test-vuejs.instructions.md` - Frontend testing guidelines
- `md.instructions.md` - Documentation writing standards
- `ps1.instructions.md` - PowerShell scripting guidelines
- `server-setup-windows.instructions.md` - Primary Windows server deployment (MWNF)
- `server-setup-ovh.instructions.md` - Secondary OVH VPS deployment
- `build-workflow.instructions.md` - Build pipeline, artifact packaging, release creation

## Common Pitfalls to Avoid

- ❌ **NEVER pipe test or build commands through any output filter.** Running `php artisan test`, `composer test`, `composer ci-*`, or any test/lint/build command through `Select-Object`, `head`, `tail`, `Out-String`, `grep`, or any other trimming filter hides failure details and forces the entire run to be repeated. Always run these commands unpiped so the full output — including failure messages, stack traces, and error context — is visible.
  - ✅ `php artisan test --testsuite=Web --no-ansi --stop-on-failure`
  - ❌ `php artisan test --testsuite=Web --no-ansi --stop-on-failure 2>&1 | Select-Object -Last 10`
- ❌ Editing files in `/api-client/` (auto-generated)
- ❌ Using `fetch`, `axios`, or local `/api-client/` in SPA Demo (use published npm package)
- ❌ Altering existing migrations (create new ones)
- ❌ Using `any` type in TypeScript
- ❌ Direct file system access (use Laravel Storage)
- ❌ Committing directly to `main` branch
- ❌ Inline SVG or custom icon components (use Heroicons)
- ❌ Creating components without tests
- ❌ Ignoring lint warnings or test failures
- ❌ Reintroducing `SearchAndPaginate` (deleted) — use `IndexListRequest` + `{Entity}IndexQuery` instead
- ❌ Using Livewire for list filtering/sorting/searching/pagination on web index pages
- ❌ Issuing Eloquent queries from Blade list, detail, or form views
- ❌ Creating an `Index*Request` web class that does not extend `IndexListRequest`
- ❌ Uploading user-supplied image binaries directly to the `public` disk or the `images` directory — uploads MUST land on the `local` disk in `image_uploads/` as an `ImageUpload` record so the `ImageUploadEvent` → `AvailableImageEvent` listener chain runs (see *Image upload pipeline — indirect upload is mandatory*)
- ❌ Creating an `AvailableImage` from a user upload — `AvailableImage` records are produced exclusively by `App\Listeners\ImageUploadListener`
- ❌ Using `FileUpload->disk('public')` in any Filament Resource / Action / Page that accepts a user image — Filament uploads MUST target `local` + `image_uploads/` and persist as an `ImageUpload`
- ❌ Using Terminal instead of VS Code tools
- ❌ Using terminal to run tests instead of VS Code testing features
- ❌ Creating scripts to alter files instead of using VS Code refactoring tools
- ❌ Using linux commands and escaping in Terminal instead of native Windows commands in PowerShell

## Additional Resources

- **Live Documentation**: https://metanull.github.io/inventory-app/
- **API Documentation**: http://localhost:8000/docs/api (when running locally)
- **README**: Comprehensive project information in `/README.md`
- **Deployment Guides**: `/deployment/` directory

---

**Remember**: This is an active development project with consistent patterns throughout. When in doubt, find a similar existing implementation and follow its pattern exactly.
