# Legacy Data Import

Node.js CLI utility for one-time migration of legacy museum database data into the new Inventory Management System via REST API.

## Setup

```bash
cd scripts/legacy-import
npm install
cp .env.example .env
# Edit .env with database credentials
```

## Configuration (.env)

```bash
# Legacy database (MySQL)
LEGACY_DB_HOST=localhost
LEGACY_DB_PORT=3306
LEGACY_DB_USER=root
LEGACY_DB_PASSWORD=

# API
API_BASE_URL=http://localhost:8000/api
API_TOKEN=                # Set via login command

# Import options
DRY_RUN=false
BATCH_SIZE=50
LOG_LEVEL=info

# Legacy images (UNC path)
LEGACY_IMAGE_PATH=\\\\virtual-office.museumwnf.org\\C$\\mwnf-server\\pictures\\images
```

## Usage

### Basic Commands

```bash
# 1. Authenticate (saves token to .env)
npm run login

# 2. Validate connections
npm run validate
```

### Granular Import Control ⭐

**List available importers:**
```bash
npm run import:list

# Or directly:
npx tsx src/index.ts import --list-importers
```

**Resume from specific importer (skip earlier ones):**
```bash
# Partner failed, fix it, then resume from Partner onwards:
npx tsx src/index.ts import --start-at partner

# Skips: language, country, default-context, project
# Runs: partner → object → monument
```

**Stop at specific importer:**
```bash
# Run up to and including partner:
npx tsx src/index.ts import --stop-at partner

# Runs: language → country → default-context → project → partner
# Skips: object, monument
```

**Run specific range:**
```bash
# Run from project to object (inclusive):
npx tsx src/index.ts import --start-at project --stop-at object

# Skips: language, country, default-context
# Runs: project → partner → object
# Skips: monument
```

**Run only one importer:**
```bash
# Run only object (useful for testing/fixing specific importer):
npx tsx src/index.ts import --only object

# Skips everything except: object
```

**Combine with dry-run:**
```bash
npx tsx src/index.ts import --start-at object --dry-run
npx tsx src/index.ts import --stop-at partner --dry-run
npx tsx src/index.ts import --only monument --dry-run
```

### Available Importers

All importers are **critical** - execution stops immediately on first error.

1. `language` - Import language reference data
2. `country` - Import country reference data
3. `default-context` - Create default context
4. `project` - Import projects and collections
5. `partner` - Import museums and institutions
6. `object` - Import object items
7. `monument` - Import monument items

### All CLI Options

```bash
npx tsx src/index.ts import \
  [--dry-run] \
  [--start-at <importer>] \
  [--stop-at <importer>] \
  [--only <importer>] \
  [--list-importers]

# Examples:
npx tsx src/index.ts import                          # Run all importers
npx tsx src/index.ts import --dry-run                # Dry-run all
npx tsx src/index.ts import --start-at partner       # Resume from partner
npx tsx src/index.ts import --stop-at object         # Stop at object
npx tsx src/index.ts import --start-at project --stop-at object  # Range
npx tsx src/index.ts import --only object            # Run only object
npx tsx src/index.ts import --list-importers         # List all
npx tsx src/index.ts login --url http://other-api.local/api  # Login
```

## Testing

```bash
# Run all unit/integration tests (mocked, non-destructive)
npm test

# Run specific test file
npm test tests/integration/phase-01/MuseumImporter.test.ts

# Watch mode for TDD
npm run test:watch

# Coverage
npm run test:coverage
```

## Verify Real Import (E2E Test)

**⚠️ DESTRUCTIVE - Creates real data in database!**

Prerequisites:
1. Legacy database accessible (check .env credentials)
2. API running locally: `php artisan serve` in main project
3. Authenticated: `npx tsx src/index.ts login`

To verify import actually works:
```bash
# 1. Edit tests/e2e/phase-01-real-import.test.ts - remove .skip from describe()
# 2. Run the E2E test
npm test tests/e2e/phase-01-real-import.test.ts

# This will:
# - Connect to REAL legacy MySQL database
# - Import actual data (limited to 2 records per entity)
# - Verify data was created via API
# - Test deduplication on repeated imports
```

## Quick Verification Checklist

✅ **Tests configured correctly** - `npm test` runs only source tests (not dist/), 6 test files, 37 tests pass
✅ **CLI works** - `npx tsx src/index.ts validate` shows connection status
✅ **Import Phase 1 implemented** - Projects → Contexts+Collections, Partners → Museums+Institutions
✅ **Type-check passes** - `npm run type-check` with no errors
✅ **Lint passes** - `npm run lint` formats code correctly

**Current Status**: Phase 1 Task 2 complete (Museums + Institutions), ready for Task 3 (Items)

## Import Phases

**Phase 1** (mwnf3 core): Projects, Partners, Items, Images, Authors, Tags  
**Phase 2** (sh/thg): Exhibitions, Galleries, Contextual translations  
**Phase 3** (travels/explore): Trails, Itineraries, Explore monuments (~1,808 records)  
**Phases 4-17**: Additional schemas, validation

See [/docs/legacy-import/LEGACY_DATA_IMPORT_TASK_PLAN.md](../../docs/legacy-import/LEGACY_DATA_IMPORT_TASK_PLAN.md)

## Architecture

```
Legacy MySQL → Importer → REST API (@metanull/inventory-app-api-client) → New Database
                  ↓
         BackwardCompatibilityTracker (deduplication)
```

**Key concepts**:
- **backward_compatibility**: `{schema}:{table}:{pk1}:{pk2}:...` (excludes language for denormalized tables)
- **Denormalization**: Legacy mwnf3/travels have language in PK → group rows, create one entity + multiple translations
- **Deduplication**: Check tracker before creating entities to avoid duplicates across schemas
- **Images**: Upload original only, create child items type='picture' for gallery contexts
- **Collections**: Import parents before children (hierarchical)

## Development

```bash
# Tests (TDD: write tests first, then implement)
npm test
npm run test:watch
npm run test:coverage

# Code quality
npm run lint
npm run type-check

# Debug
LOG_LEVEL=debug npx tsx src/index.ts import --phase 1
npx tsx src/index.ts import --phase 1 --dry-run --limit 10
```

### Adding Importers

1. Write test in `tests/integration/phase-XX/` (RED)
2. Implement in `src/importers/phase-XX/` (GREEN)
3. Refactor

See [docs/DEVELOPMENT.md](docs/DEVELOPMENT.md) for patterns, examples, pitfalls.

## Directory Structure

```
src/
├── index.ts                    # CLI commands
├── database/LegacyDatabase.ts  # MySQL connector
├── api/InventoryApiClient.ts   # API wrapper
├── utils/
│   ├── BackwardCompatibilityFormatter.ts
│   └── BackwardCompatibilityTracker.ts
└── importers/
    ├── BaseImporter.ts
    ├── phase-01/               # mwnf3 core
    ├── phase-02/               # sh, thg
    └── phase-03/               # travels, explore

tests/
├── unit/
├── integration/
│   ├── phase-01-mwnf3-core/
│   ├── phase-02-sh-thg/
│   └── phase-03-travel-explore/
└── e2e/
```

## References

- [MASTER-Import-Strategy-And-Mapping.md](../../docs/legacy-import/MASTER-Import-Strategy-And-Mapping.md) - Entity mappings, hierarchies
- [LEGACY_DATA_IMPORT_TASK_PLAN.md](../../docs/legacy-import/LEGACY_DATA_IMPORT_TASK_PLAN.md) - 17 phases, 170 tasks
- [docs/DEVELOPMENT.md](docs/DEVELOPMENT.md) - TDD workflow, patterns, examples
