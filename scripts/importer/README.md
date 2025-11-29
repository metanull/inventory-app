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

Create a `.env` file with:

```env
# Legacy Database (source)
LEGACY_DB_HOST=localhost
LEGACY_DB_PORT=3306
LEGACY_DB_USER=root
LEGACY_DB_PASSWORD=secret
LEGACY_DB_DATABASE=mwnf3

# New Database (target)
DB_HOST=localhost
DB_PORT=3306
DB_USERNAME=root
DB_PASSWORD=secret
DB_DATABASE=inventory
```

## Adding New Importers

1. Create legacy type in `domain/types/legacy.ts`
2. Create transformer in `domain/transformers/`
3. Create importer in `importers/phase-XX/`
4. Add to CLI registry in `cli/import.ts`

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
