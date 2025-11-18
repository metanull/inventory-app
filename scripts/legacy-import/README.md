# Legacy Data Import Utility

CLI application for importing legacy museum database data into the new Inventory Management System.

## Purpose

This is a **standalone technical utility** for one-time data migration. It is **NOT** a feature of the main application.

## Features

- ✅ Reads from legacy MySQL databases (mwnf3, sh, thg, travels, explore)
- ✅ Writes via published API client (@metanull/inventory-app-api-client)
- ✅ Deduplication via backward_compatibility tracking
- ✅ Handles denormalized legacy data (language in PK)
- ✅ Imports hierarchical collections
- ✅ Imports images with tree-structured picture items
- ✅ TDD workflow with Vitest
- ✅ Dry-run mode for validation
- ✅ Phase-based execution (17 phases)

## Quick Start

### 1. Install Dependencies

```bash
cd scripts/legacy-import
npm install
```

### 2. Configure Environment

```bash
cp .env.example .env
# Edit .env with your database credentials
```

Required settings:
- `LEGACY_DB_HOST`, `LEGACY_DB_PORT`, `LEGACY_DB_USER`, `LEGACY_DB_PASSWORD`
- `API_BASE_URL`

### 3. Login to API

```bash
npm start -- login
```

This will:
- Prompt for username/password
- Authenticate with the Inventory API
- Save the access token to `.env`
- Test the token validity

### 4. Validate Connections

```bash
npm start -- validate
```

### 4. Run Import

```bash
# Dry run (recommended first)
npm start -- import --phase 1 --dry-run

# Actual import
npm start -- import --phase 1

# All phases
npm start -- import
```

## Commands

```bash
# Login to API (get access token)
npm start -- login
npm start -- login --url http://custom-api.local/api

# Validate connections (API + legacy database)
npm start -- validate

# Import specific phase
npm start -- import --phase <1-17>

# Dry run (no data written)
npm start -- import --dry-run

# Limit records (for testing)
npm start -- import --limit 100

# Check import status
npm start -- status
```

## Development

See [docs/DEVELOPMENT.md](docs/DEVELOPMENT.md) for comprehensive guide covering:
- Architecture and data flow
- backward_compatibility format rules
- Denormalization handling
- Deduplication strategy
- Image import strategy
- Collection hierarchies
- TDD workflow
- Adding new importers

### Run Tests

```bash
# All tests
npm test

# Watch mode (TDD)
npm run test:watch

# With coverage
npm run test:coverage

# Specific test file
npm test tests/unit/BackwardCompatibilityFormatter.test.ts
```

### Type Checking

```bash
npm run type-check
```

### Linting

```bash
npm run lint
```

## Import Phases

Based on [LEGACY_DATA_IMPORT_TASK_PLAN.md](../../docs/legacy-import/LEGACY_DATA_IMPORT_TASK_PLAN.md):

### Phase 1: mwnf3 Core Schema
- Projects → Contexts + Collections
- Partners (museums, institutions)
- Items (objects, monuments, details)
- Images, Authors, Tags

### Phase 2: Sharing History & Thematic Gallery
- Exhibitions/Themes/Subthemes
- Galleries
- Contextual translations

### Phase 3: Travel & Explore
- Travel trails/itineraries/locations
- Explore monuments (CRITICAL - ~1,808 monuments)
- Explore itineraries

### Phases 4-17: Additional schemas and final validation

## Architecture

```
Legacy MySQL → LegacyDatabase → Importer → API Client → New Model
                                     ↓
                          BackwardCompatibilityTracker
                            (deduplication)
```

### Key Components

- **LegacyDatabase**: MySQL connector for legacy schemas
- **InventoryApiClient**: Wrapper for published npm API client
- **BackwardCompatibilityFormatter**: Format legacy references
- **BackwardCompatibilityTracker**: Track imported entities, avoid duplicates
- **BaseImporter**: Abstract class for all importers
- **Phase importers**: Entity-specific import logic

## Testing

Tests organized by import phases:

```
tests/
├── unit/                      # Utility functions
├── integration/               # Importer classes
│   ├── phase-01-mwnf3-core/
│   ├── phase-02-sh-thg/
│   └── phase-03-travel-explore/
└── e2e/                       # Full import runs
```

### TDD Workflow

1. Write test FIRST (RED)
2. Implement importer (GREEN)
3. Refactor (REFACTOR)

All importers must have comprehensive test coverage before implementation.

## Configuration

### Environment Variables

```bash
# Legacy MySQL Database
LEGACY_DB_HOST=localhost
LEGACY_DB_PORT=3306
LEGACY_DB_USER=root
LEGACY_DB_PASSWORD=

# New Inventory API
API_BASE_URL=http://localhost:8000/api
API_TOKEN=

# Import Configuration
DRY_RUN=true
BATCH_SIZE=50
LOG_LEVEL=info

# Legacy Images (Windows UNC path)
LEGACY_IMAGE_PATH=\\\\virtual-office.museumwnf.org\\C$\\mwnf-server\\pictures\\images
```

## References

- **Analysis Documents**: [/docs/legacy-import/](../../docs/legacy-import/)
  - `MASTER-Import-Strategy-And-Mapping.md` - Entity mappings, hierarchies, image strategy
  - `LEGACY_DATA_IMPORT_TASK_PLAN.md` - 17 phases, 170 tasks
- **Development Guide**: [docs/DEVELOPMENT.md](docs/DEVELOPMENT.md)
- **Test Structure**: [tests/README.md](tests/README.md)

## License

Same as parent project.
