# Inventory Management API - Copilot Instructions

## Project Overview

The **Inventory Management API** is a comprehensive Laravel 12 + Vue.js 3 application providing RESTful APIs for museum inventory management at Museum With No Frontiers. This is a monorepo containing:

- **Backend API**: Laravel 12 (PHP 8.2+) with Sanctum authentication
- **Frontend SPA**: Vue 3 + TypeScript with Pinia state management  
- **Documentation Site**: Jekyll-based static site in `/docs/`
- **TypeScript API Client**: Auto-generated from OpenAPI spec in `/api-client/`

### Key Technologies

- **Backend**: Laravel 12, PHP 8.2+, Eloquent ORM, Sanctum, Blade templates
- **Frontend**: TypeScript, Vue 3, Vite, Pinia, Tailwind CSS
- **Database**: SQLite (development), MariaDB (production) with UUID primary keys
- **Testing**: PHPUnit (backend), Vitest (frontend) - 560+ tests, 100% reliable
- **Tooling**: Composer, npm, Laravel Pint, ESLint, Prettier, GitHub Actions

## Architecture & Structure

### Directory Layout

```
/app/                   # Laravel application code (Models, Controllers, etc.)
/routes/api.php         # REST API route definitions
/resources/js/          # Vue 3 SPA frontend application
  /components/          # Reusable Vue components
  /stores/              # Pinia state management stores
  /views/               # Page-level Vue components
  /__tests__/          # Vitest test suites
/database/             # Migrations, factories, seeders
/tests/                # PHPUnit backend tests
/docs/                 # Jekyll documentation site (Ruby-based)
/api-client/           # Auto-generated TypeScript API client (DO NOT EDIT)
/.github/instructions/ # File-specific coding guidelines
```

### Application Components

1. **REST API Backend** (`/api` routes)
   - Pure API backend with no frontend code
   - Sanctum authentication for all endpoints
   - Resource controllers with Form Request validation
   - OpenAPI/Swagger documentation at `/api/docs`

2. **Web Frontend** (`/web` routes)
   - Server-rendered Blade templates with Livewire
   - Direct Laravel model/controller interactions (NOT via API)
   - Alpine.js and Tailwind CSS

3. **SPA Demo** (`/cli` routes)
   - Vue 3 + TypeScript SPA demonstrating API usage
   - Source in `/resources/js/`
   - Uses generated TypeScript API client exclusively
   - Pinia for state management

## Development Standards

### Code Quality Requirements

- **All code must pass linting**: Laravel Pint (PHP), ESLint (JS/TS)
- **All tests must pass**: No failing tests allowed in PRs
- **No TypeScript errors or warnings**: Must pass `npx tsc --noEmit`
- **No unused variables or imports**: Enforced by linters
- **Explicit typing**: Never use `any` type in TypeScript

### Essential Commands

```bash
# Install dependencies
composer install
npm ci --no-audit --no-fund

# Database setup
php artisan migrate --seed

# Build frontend
npm run build

# Development servers
php artisan serve        # Laravel API (port 8000)
npm run dev             # Vite frontend

# Linting
composer ci-lint        # Laravel Pint (PHP)
npm run lint           # ESLint + Prettier (JS/TS/Vue)

# Testing
php artisan test --parallel    # Backend tests
npm run test                   # Frontend tests
```

### Git Workflow

- **Never commit directly to `main`**: Use feature branches with PRs
- **Branch naming**: Use `feature/` or `fix/` prefix
- **Before starting**: Always pull latest `main` to work on current code
- **Before PR**: Ensure all lints pass and all tests pass locally

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

All controllers follow standard patterns:
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

## Vue.js Frontend Conventions

### Component Structure

- **Always use**: `<script setup lang="ts">` for all SFCs
- **Composition API only**: Use `ref`, `computed`, `watch`, etc.
- **Type everything**: Props, emits, state - never use `any`
- **Scoped styles**: `<style scoped>` for component-specific CSS

### State Management

- **Pinia stores**: For ALL shared state (never use component `data` or `reactive`)
- **Store patterns**: Follow existing patterns in `/resources/js/stores/`
- **API calls**: Use generated client from `/api-client/` - NEVER use `fetch` or `axios` directly

### Reusable Components

Extract repeated UI patterns into shared components. Key component categories:

- **Layout**: ListView, DetailView, AppHeader, AppFooter
- **Cards**: Card, NavigationCard, StatusCard, InformationCard
- **Forms**: FormInput, GenericDropdown, Toggle
- **Display**: DisplayText, InternalName, DateDisplay, Title, Uuid
- **Tables**: TableElement, TableHeader, TableRow, TableCell

See `/resources/js/components/` for complete component library.

### Styling

- **Tailwind CSS**: Primary styling method
- **Responsive design**: Use Tailwind breakpoints (`sm:`, `md:`, `lg:`, `xl:`)
- **Entity colors**: Consistent across navigation, pages, and components
  - Items: `teal`, Partners: `yellow`, Languages: `purple`
  - Countries: `blue`, Contexts: `green`, Projects: `orange`

### Icons

- **Heroicons ONLY**: Import from `@heroicons/vue/24/solid` or `/24/outline`
- **NO inline SVG**: Never create custom icon components
- **Semantic aliases**: Use descriptive names (e.g., `import { CogIcon as ContextIcon }`)

## Testing Standards

### Backend Tests (PHPUnit)

Test organization in `/tests/`:
- `/Unit/` - Factory tests, model validation
- `/Feature/` - API endpoint tests organized by model

Every model requires test files:
- `AnonymousTest.php` - Unauthorized access scenarios
- `IndexTest.php` - List operations
- `ShowTest.php` - Single record retrieval
- `StoreTest.php` - Record creation
- `UpdateTest.php` - Record updates
- `DestroyTest.php` - Record deletion

Test requirements:
- Use `RefreshDatabase` trait
- Use factories for test data (`.create()` for DB, `.make()->toArray()` for requests)
- Authenticate with Sanctum: `$this->actingAs(User::factory()->create())`
- Validate with `assertJsonStructure`, `assertJsonPath`, `assertOk`, etc.

### Frontend Tests (Vitest)

Test organization in `/resources/js/__tests__/`:
- `/feature/` - Component functionality and behavior
- `/integration/` - Component integration and workflows
- `/logic/` - Business logic and computations
- `/resource_integration/` - API resource integration (`.tests.ts` suffix)
- `/consistency/` - Cross-entity consistency validation

Test requirements:
- **Small and focused**: Single objective per test
- **Deterministic**: No randomness or time-based dependencies
- **Independent**: No reliance on other test state
- **Explicit mocking**: All external dependencies faked/mocked per-test
- **Reference example**: Use `Contexts.test.ts` as canonical pattern

## API Client Generation

The TypeScript client in `/api-client/` is auto-generated - **NEVER edit manually**.

### Generation Process

```powershell
# Generate client from OpenAPI spec
.\scripts\generate-api-client.ps1

# Publish to GitHub Packages (requires PAT)
.\scripts\publish-api-client.ps1 -Credential (Get-Credential)
```

Steps performed:
1. Generate OpenAPI spec: `composer ci-openapi-doc` → `docs/_openapi/api.json`
2. Generate TypeScript client: `openapi-generator-cli` → `/api-client/`

### Using the Client

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

All Markdown files must include:
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
   - Backend: `Context` model, controller, tests in `/tests/Feature/Api/Context/`
   - Frontend: `Contexts.vue` page and `Contexts.test.ts` tests
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

## Common Pitfalls to Avoid

- ❌ Editing files in `/api-client/` (auto-generated)
- ❌ Using `fetch` or `axios` in Vue SPA (use generated client)
- ❌ Altering existing migrations (create new ones)
- ❌ Using `any` type in TypeScript
- ❌ Direct file system access (use Laravel Storage)
- ❌ Committing directly to `main` branch
- ❌ Inline SVG or custom icon components (use Heroicons)
- ❌ Creating components without tests
- ❌ Ignoring lint warnings or test failures

## Additional Resources

- **Live Documentation**: https://metanull.github.io/inventory-app/
- **API Documentation**: http://localhost:8000/docs/api (when running locally)
- **README**: Comprehensive project information in `/README.md`
- **Deployment Guides**: `/deployment/` directory

---

**Remember**: This is an active development project with consistent patterns throughout. When in doubt, find a similar existing implementation and follow its pattern exactly.
