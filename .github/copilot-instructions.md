# Copilot Coding Agent Instructions (concise)

This repo is a Laravel 12 (PHP 8.2+) backend and Vue 3 + TypeScript SPA frontend monorepo. The guidance below focuses on project-specific rules, commands, and locations an automated coding agent needs to be productive.

- Workspace layout: key folders are `app/` (Laravel code), `routes/api.php` (REST surface), `resources/js/` (Vue app + Pinia stores and `resources/js/**/__tests__` for Vitest tests), `database/` (migrations, factories, seeders), `docs/` (Jekyll site - Ruby, built via WSL), `docs/_openapi/api.json` (generated API specification), `docs/_docs`(generated commit history) and `api-client/` (generated TypeScript client).

- Environment & shell: Windows + PowerShell only. 
  - Never use Unix commands; prefer PowerShell equivalents (Get-ChildItem, Get-Content, Copy-Item, etc.). 
  - For special characters, do not use Unix escaping (backslash "\"), use Powershell escaping (backtick "`")
  - For Ruby and Jekyll run commands via Windows Substystem for Linux via `wsl bash -lc '...'`.

- Tests:
  - **CRITICAL**: Always use VS Code testing features to run tests.
  - If exceptionally you must run test in a terminal, then 
    - for Backend tests: use Laravel's test runner directly: `php artisan test` (always with `--parallel` and with `--compact` when helpful).
    - for Frontend tests (Vitest): run `vitest run` (prefer `--reporter=dot` for CI-friendly output). Vitest tests live under `resources/js/**/__tests__` alongside the Vue app.
  - Testing in mandatory before push and PR.
  
- CI:
  - CI workflows live in `.github/workflows/` — `laravel.yml` is the main CI.

- Linting & pre-commit checks
  - Backend checks: 
    - run Pint directly: `\.\vendor\bin\pint` or `php artisan pint` if configured.
  - Frontend checks: 
    - run ESLint directly: `npx eslint . --ext .ts,.vue` or via project npm scripts (`npm run lint`).
    - run typescript checks via npm scripts (`npm run type-check`).
    - run build via `npm run build`.
  - Security audits: 
    - run `composer audit` for PHP dependencies
    - run `npm audit` (or `npm audit --audit-level=moderate`) for Node dependencies.
  - Checking is mandatory before push and PR. No warnings or errors must be ignored.

- Git rules and branching (enforced by repo rules):
  - When starting a new job
    - Always pull the latest `main` to ensure you're working on the latest code.
    - Create a branch with `feature/` or `fix/` prefix.
  - Never push/commit directly to `main`. 
    - Use `gh pr create` for PRs, preferably with the option `--assignee "@me"`; commit/pr messages are often stored in temporary markdown files (`temp_*.md`) to avoid escaping issues.
    - Before creation of a PR, right after the push, run semantic versioning (`npm version patch|minor|major`) and update `CHANGELOG.md`.

- Laravel conventions to preserve when modifying/adding models or APIs:
  - Every new model must include Migration, Resource, Controller, Factory, Seeder and Tests.
  - Never alter existing migrations; create new migrations for schema changes.
  - UUID primary keys are used for most models (see `HasUuids` usage pattern in `app/Models`).

- Conventions for frontend state management (Pinia) and API client usage:
  - Use Pinia stores for all frontend state management; do not use Vue's `data` or `reactive` for shared state.
  - Use the generated API client in `api-client/` for all API interactions; do not use `fetch` or `axios` directly.
  - Follow existing store patterns for actions, getters, and state organization.

- Patterns & examples to follow (search these files when implementing changes):
  - REST surface and auth: `routes/api.php` (routes are authenticated via Sanctum).
  - Frontend state & tests: `resources/js/` and `resources/js/**/__tests__/` (consistency/feature/integration/logic directories).
  - Generated API client: `api-client/` (OpenAPI generated artifacts). The client is produced by a two-step process wrapped in `scripts/generate-api-client.ps1`: 1) produce the OpenAPI JSON (api.json) using the project's OpenAPI export step (e.g. `dedoc:scramble`), then 2) run `openapi-generator-cli` to generate the TypeScript client. Prefer invoking the wrapper script rather than reimplementing the steps.

- Frontend testing conventions (REQUIRED):
  - Tests should be small and focused on a single objective.
  - Tests must be deterministic: avoid randomness, time-based dependencies, or external state that can lead to flaky tests.
  - Tests must be independent from each other: do not rely on state left by other tests.
  - Tests must not rely on unstated assumptions: any external dependency must be faked/mocked explicitly within the test (mock data and mock functions defined per-test).
  - Use the existing `Context` tests in `resources/js/**/__tests__/feature/Contexts.test.ts` and resource_integration tests as examples for mocking `useContextStore`, verifying effects, and arranging assertions.

- Strict validation rules (ENFORCED):
  - All tests must pass locally before creating a PR. No failing tests allowed.
  - All lint warnings/errors must be fixed (backend and frontend). Do not bypass lint checks.
  - All TypeScript warnings and errors must be addressed.
  - Add these checks to your local pre-PR routine (run tests, eslint, and `npx tsc --noEmit`) and ensure they are green before creating a PR.

- Safety & non-goals for agents:
 
 - Generated-code consistency (REQUIRED):
   - Any generated or newly implemented code MUST preserve consistency with existing patterns. Before implementing, analyze the existing implementation (models, controllers, stores, and tests for the backend; components and tests for the frontend). Compare naming, validation, API shape, error handling, and test patterns. Use `Context` as the canonical example: the backend model, controllers, and `tests/Feature/Api/Context/*` as well as the frontend `resources/js/stores/context` and `resources/js/**/__tests__/feature/Contexts.test.ts` illustrate the expected structure and testing style. New code should follow these patterns closely.
- Do not create repo-wide automated search-and-replace scripts. Make atomic, reviewed edits.

- Conventions for documentation (Jekyll):
    - Layouts are stored in `_layouts`. Each Markdown file should specify a `layout` in its front matter, e.g. `layout: default`.
    - The main configuration for Jekyll is in `_config.yml`.
    - Every Markdown file in `/docs` (and subfolders) must begin with a YAML front matter block. Required front matter fields:
        - `layout`: Specifies which layout to use from `_layouts`.
        - `title`: The page title, used in navigation and page headers.
        - `nav_order`: Used to control navigation order.
        - Optional fields: `description`, `permalink`, `parent`, `has_children`, etc., depending on the site's navigation structure.
    - Every Directory in `/docs` that should appear in navigation must contain an `index.md` file with appropriate front matter.
    - Keep the navigation structure in sync with the actual files and their front matter.
    - Hyperlinks
        - Links to a directory must include the trailing slash. By example `[API Models](api-models/)` is a link to the file `api-models/index.md`
        - Links to a file must omit the trailing extension. By example `[guidelines](guidelines)` is a link to the file `guidelines.md` 
        - For cross-references between sections, prefer linking to the Markdown file (not the generated HTML), unless a permalink is defined in the front matter.
        - Internal links should use relative paths.
        - Absolute links should start with `/` and omit the `/docs` prefix. By example, a link to `/docs/guidelines/index.md` should be written as `[Guidelines](/guidelines)`.
        - Linking to non markdown file require the creation of a markdown placeholder file, with `permalink` and `layout: null` frontmatter fields.
            - By example, a to make the OpenAPI spec file `/docs/_openapi/api.json` available as '/api.json' on the generated website, create a file `docs/api.json.md` with the following content:
              ```
              ---
              layout: null
              permalink: /api.json
              ---
              {% include_relative _openapi/api.json %}
              ```
            - To create an hyperlink to that file, use its permalink's value `[OpenAPI Spec](/api.json)`.