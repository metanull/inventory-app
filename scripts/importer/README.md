# Unified Legacy Importer

A unified import tool for migrating data from the legacy MWNF database to the new Inventory Management System.

## Architecture

This importer follows a clean architecture with clear separation of concerns:

```
┌─────────────────────────────────────────────────────────────┐
│                    BaseImporter (Abstract)                   │
│  - import(): Promise<ImportResult>                           │
│  - transform helpers                                         │
│  - logging infrastructure                                    │
└──────────────────────┬──────────────────────────────────────┘
                       │
         ┌─────────────┴─────────────┐
         │                           │
┌────────▼────────┐         ┌───────▼──────────┐
│ SqlWriteStrategy │         │ (Future)         │
│ - Direct SQL     │         │ ApiWriteStrategy │
│ - Fast bulk ops  │         │ - REST API calls │
└─────────────────┘         └──────────────────┘
```

### Legacy Database Interface

The `ILegacyDatabase` interface (defined in `core/base-importer.ts`) provides read access to the legacy database:

```typescript
interface ILegacyDatabase {
  query<T>(sql: string): Promise<T[]>;
  connect(): Promise<void>;
  disconnect(): Promise<void>;
}
```

**Important Notes:**
- Queries must use actual legacy table names (e.g., `mwnf3.objects`, `mwnf3.museums`)
- The legacy schema differs from the new schema - transformers handle mapping
- Languages and countries are loaded from JSON files, NOT from the legacy database

### Directory Structure

```
src/
├── core/                   # Core interfaces and abstractions
│   ├── types.ts           # Data types and interfaces
│   ├── tracker.ts         # Entity tracking (ITracker, UnifiedTracker)
│   ├── strategy.ts        # Write strategy interface
│   └── base-importer.ts   # Base importer class
├── domain/
│   ├── types/             # Legacy data types
│   │   └── legacy.ts      # LegacyObject, LegacyMuseum, etc.
│   └── transformers/      # Business logic (pure functions)
│       ├── language-transformer.ts
│       ├── country-transformer.ts
│       ├── project-transformer.ts
│       ├── museum-transformer.ts
│       ├── institution-transformer.ts
│       ├── object-transformer.ts
│       └── monument-transformer.ts
├── strategies/             # Write strategy implementations
│   └── sql-strategy.ts    # SQL-based write strategy
├── helpers/                # Shared helper classes
│   ├── tag-helper.ts
│   ├── author-helper.ts
│   └── artist-helper.ts
├── importers/              # Importer implementations
│   ├── phase-00/          # Reference data (languages, countries)
│   └── phase-01/          # Core data (projects, partners, items)
├── utils/                  # Utility functions
│   ├── backward-compatibility.ts
│   ├── code-mappings.ts
│   └── html-to-markdown.ts
└── cli/                    # CLI entry points
    └── import.ts
```

## Key Design Principles

### 1. Single Source of Truth for Business Logic

All transformation logic is in the `domain/transformers/` directory. These are **pure functions** that:
- Take legacy data as input
- Return transformed data ready for persistence
- Have no side effects
- Can be easily tested in isolation

### 2. Strategy Pattern for Write Operations

The `IWriteStrategy` interface abstracts how data is written:
- `SqlWriteStrategy`: Direct SQL INSERT statements (fast)
- `ApiWriteStrategy`: REST API calls (future, for validation)

Importers don't know which strategy is being used - they just call `strategy.writeItem()`.

### 3. Unified Tracker

The `ITracker` interface provides a consistent way to track imported entities:
- Prevents duplicate imports
- Resolves dependencies between entities
- Works with both in-memory Map and persistent storage

### 4. DRY (Don't Repeat Yourself)

Business logic is written once in transformers:
- HTML to Markdown conversion
- Field truncation and validation
- EPM description2 handling
- Tag parsing logic

## Usage

### Installation

```bash
cd scripts/importer
npm install
```

### Running Imports

```bash
# Run all importers
npm run import

# Dry run (simulate without writing)
npm run import -- --dry-run

# Run specific importer only
npm run import -- --only partner

# Run from a specific point
npm run import -- --start-at project

# List available importers
npm run import -- --list-importers
```

### Validation

```bash
# Validate database connections
npm run import -- validate
```

## Import Order

1. **Phase 0: Reference Data**
   - `language` - Languages
   - `language-translation` - Language name translations
   - `country` - Countries
   - `country-translation` - Country name translations

2. **Phase 1: Core Data**
   - `project` - Projects (creates Context + Collection + Project)
   - `partner` - Partners (Museums + Institutions)
   - `object` - Object items
   - `monument` - Monument items

## Environment Variables

Create a `.env` file in the `scripts/importer` directory with:

```env
# Legacy Database (source) - for reading projects, partners, and items
LEGACY_DB_HOST=localhost
LEGACY_DB_PORT=3306
LEGACY_DB_USER=root
LEGACY_DB_PASSWORD=secret
LEGACY_DB_DATABASE=mwnf3

# New Database (target) - where data will be imported to
DB_HOST=localhost
DB_PORT=3306
DB_USERNAME=root
DB_PASSWORD=secret
DB_DATABASE=inventory
```

### Required Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `LEGACY_DB_HOST` | Legacy database hostname | `localhost` |
| `LEGACY_DB_PORT` | Legacy database port | `3306` |
| `LEGACY_DB_USER` | Legacy database username | `root` |
| `LEGACY_DB_PASSWORD` | Legacy database password | (empty) |
| `LEGACY_DB_DATABASE` | Legacy database name | `mwnf3` |
| `DB_HOST` | Target database hostname | `localhost` |
| `DB_PORT` | Target database port | `3306` |
| `DB_USERNAME` | Target database username | `root` |
| `DB_PASSWORD` | Target database password | (empty) |
| `DB_DATABASE` | Target database name | `inventory` |

### Validating Database Connections

Before running the import, validate your database connections:

```bash
npm run import -- validate
```

This will test both legacy and target database connections and report any issues.

## Data Sources

### Reference Data (Languages & Countries)
Languages and countries are **NOT** imported from the legacy database. Instead, they are loaded from production JSON files:
- `database/seeders/data/languages.json` - ISO 639-3 language codes
- `database/seeders/data/countries.json` - ISO 3166-1 alpha-3 country codes

These files are the same sources used by Laravel seeders and the API importer.

### Legacy Database Data
The following entities are imported from the legacy database (`mwnf3`):
- **Projects**: `mwnf3.projects`, `mwnf3.projectnames`
- **Museums**: `mwnf3.museums`, `mwnf3.museumsnames`
- **Institutions**: `mwnf3.institutions`, `mwnf3.institutionnames`
- **Objects**: `mwnf3.objects`
- **Monuments**: `mwnf3.monuments`

## Adding New Importers

1. Create legacy type in `domain/types/legacy.ts`
2. Create transformer in `domain/transformers/`
3. Create importer in `importers/phase-XX/`
4. Add to CLI registry in `cli/import.ts`

## Sample Collection

**Note:** Sample collection for test fixtures is not currently implemented in this unified importer. The interface `ISampleCollector` exists in `core/base-importer.ts` but is not active.

If you need to collect sample data for testing, use the legacy importer at `scripts/legacy-import/sql-import-v2.ts` which has full sample collection support via the `--collect-samples` flag.

## Extending with API Strategy

To add API-based imports:

1. Create `strategies/api-strategy.ts` implementing `IWriteStrategy`
2. Create CLI option to select strategy
3. Importers automatically work with new strategy

## Benefits of This Architecture

✅ **Single Source of Truth** - Business logic in one place  
✅ **Easy Testing** - Mock strategies, test transformers separately  
✅ **Flexible** - Switch strategies at runtime  
✅ **Extensible** - New strategies without touching business logic  
✅ **DRY** - No code duplication between API/SQL approaches  
✅ **KISS** - Simple, focused components  
✅ **Single Responsibility** - Each component does one thing well
