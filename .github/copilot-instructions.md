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

VS Code task runs are terminal-based and do not feed results back into the Testing panel. For interactive PHP test discovery and per-test results inside VS Code, use the `PHPUnit & Pest Test Explorer` extension and run tests from the Testing view instead of from tasks.

**SPA commands** (run from `/spa/` directory):
```powershell
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
