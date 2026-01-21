# Unified Legacy Importer

A unified import tool for migrating data from the legacy MWNF database to the new Inventory Management System.

**Current Status**: Completes import of the **mwnf3** database (projects, partners, items, images). Other legacy databases (travels, sh, thg, explore) remain to be implemented.

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

### Data Flow

The import process follows a clear data flow:

```
┌──────────────────┐
│  Legacy Database │  (mwnf3.objects, mwnf3.monuments, etc.)
│   + JSON Files   │  (languages.json, countries.json)
└────────┬─────────┘
         │
         │ ILegacyDatabase.query()
         ▼
┌──────────────────┐
│   Transformer    │  (Pure functions in domain/transformers/)
│  - HTML→Markdown │
│  - Field mapping │
│  - Validation    │
└────────┬─────────┘
         │
         │ Transformed data
         ▼
┌──────────────────┐
│  Write Strategy  │  (SqlWriteStrategy with resilient connection)
│  - SQL INSERT    │
│  - Retry logic   │
│  - Transaction   │
└────────┬─────────┘
         │
         ▼
┌──────────────────┐
│  Target Database │  (inventory.items, inventory.partners, etc.)
└──────────────────┘
```

### Project Creation

Projects have special handling - each legacy project creates THREE new entities:

1. **Context** - Contextual grouping (e.g., "WAL Project")
2. **Collection** - Grouping of items (e.g., "WAL Collection")
3. **Project** - Actual project entity (e.g., "Umayyad Route")

All three share the same `backward_compatibility` value (e.g., `mwnf3:projects:WAL`) to facilitate linking.

### Directory Structure

```
src/
├── core/                   # Core interfaces and abstractions
│   ├── types.ts           # Data types and interfaces
│   ├── tracker.ts         # Entity tracking (ITracker, UnifiedTracker)
│   ├── strategy.ts        # Write strategy interface
│   ├── file-logger.ts     # Dual console/file logging
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
│       ├── monument-transformer.ts
│       └── monument-detail-transformer.ts
├── strategies/             # Write strategy implementations
│   └── sql-strategy.ts    # SQL-based write strategy
├── helpers/                # Shared helper classes
│   ├── tag-helper.ts
│   ├── author-helper.ts
│   └── artist-helper.ts
├── importers/              # Importer implementations
│   ├── phase-00/          # Reference data (languages, countries, default context)
│   ├── phase-01/          # Core data (projects, partners, items)
│   └── phase-02/          # Images (item pictures, partner pictures)
├── tools/                  # Utility tools
│   └── image-sync.ts      # Image file synchronization
├── utils/                  # Utility functions
│   ├── backward-compatibility.ts
│   ├── code-mappings.ts
│   ├── html-to-markdown.ts
│   └── image-sync.ts      # Image sync utilities
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

**Example**: `monument-detail-transformer.ts` converts legacy monument details into Items with proper type='detail' and parent relationships.

### 2. Strategy Pattern for Write Operations

The `IWriteStrategy` interface abstracts how data is written:

- `SqlWriteStrategy`: Direct SQL INSERT statements (fast, resilient with retry logic)
- `ApiWriteStrategy`: REST API calls (future, for validation)

Importers don't know which strategy is being used - they just call `strategy.writeItem()`.

### 3. Unified Tracker

The `ITracker` interface provides a consistent way to track imported entities:

- Prevents duplicate imports
- Resolves dependencies between entities
- Tracks default language and default context
- Works with both in-memory Map and persistent storage

### 4. Resilient Connections

The importer uses `ResilientConnection` with automatic retry logic:

- Reconnects on connection loss
- Retries failed queries up to 3 times
- Handles database timeouts gracefully

### 5. Comprehensive Logging

All import operations are logged to both console and timestamped files in `logs/`:

- Detailed progress tracking
- Error context with full stack traces
- Phase summaries with duration and counts
- Warning tracking for data quality issues

### 6. DRY (Don't Repeat Yourself)

Business logic is written once in transformers:

- HTML to Markdown conversion
- Field truncation and validation
- EPM description2 handling
- Tag parsing logic
- Artist extraction

## Usage

### Typical Workflow

The importer is designed to run as part of a complete database initialization:

1. **Wipe database** - Create or Empty the database schema (e.g. `artisan db:wipe; artisan migrate`)
2. **Run the importer** - Import legacy data from mwnf3 database
3. **Done** - Database is ready with both reference and legacy data

All operations are logged to timestamped files in the `logs/` directory for later review.

### Running the importer

```bash
cd scripts/importer
npm install                # First run only (installs dependencies)
npx tsx src/cli/import.ts import
```

### Available Commands

The importer CLI provides three main commands: import, validate, image-sync.

#### Quick Reference

```bash
# Get help on the importer
npx tsx src/cli/import.ts --help

# Show command-specific help
npx tsx src/cli/import.ts import --help
npx tsx src/cli/import.ts validate --help
npx tsx src/cli/import.ts image-sync --help
```

#### 1. Import Command - Import legacy data

```bash
# Run all importers
npx tsx src/cli/import.ts import
```

**Additional paramters:**

- `--help` - Syntax help
- `--list-importers` - List available importers
- `--dry-run` - Dry run (simulate without writing)
- `--only {importer}` - Run specific importer only (e.g. `--only partner`)
- `--start-at {importer}` - Run from a specific point onwards (e.g. `--start-at project`)
- `--stop-at {importer}` - Run up to and including a specific point (e.g. `--stop-at partner`)

#### 2. Validate Command - Test database connections

```bash
# Validate database connections
npx tsx src/cli/import.ts validate
```

#### 3. Image Sync Command - Synchronize legacy images

```bash
# Copy legacy images to new storage
npx tsx src/cli/import.ts image-sync
```

**Additional parameters:**

- `--help` - Syntax help
- `--symlink` - Use symbolic links instead of copying (faster, for testing)
- `---dry-run-` - Dry run (simulate without making changes)

**Image Sync Details:**

- Finds ItemImage and PartnerImage records with `size=1` (legacy placeholders)
- Copies or symlinks actual image files from legacy storage
- Updates database records with correct path, size, and metadata
- Only connects to the new database (not the legacy database)
- Requires `LEGACY_IMAGES_ROOT` environment variable

### Logging

All import operations are logged to timestamped files in the `logs/` directory:

```
logs/
├── import-2025-12-13T20-47-35-804Z.log
├── import-2025-12-13T21-07-20-361Z.log
└── import-2025-12-14T01-30-07-715Z.log
```

**Log Contents**:

- Import start time and configuration
- Per-importer progress (imported, skipped, errors)
- Warnings with context
- Error details with stack traces
- Phase summaries with durations
- Final statistics

**Console Output**:

- Real-time progress indicators (`.` = imported, `s` = skipped, `×` = error)
- Phase headers and summaries
- Final success/failure status

## Import Order

Importers run in strict sequence to satisfy dependencies. Each importer logs detailed information to timestamped files in the `logs/` directory.

### **Phase 0: Reference Data**

Foundation data loaded from JSON files and legacy database translations:

- `default-context` - Create default context (is_default=true)
- `language` - Languages from JSON file
- `language-translation` - Language name translations from legacy DB
- `country` - Countries from JSON file
- `country-translation` - Country name translations from legacy DB

### **Phase 1: Core Data**

Primary entities imported from legacy mwnf3 database:

- `project` - Projects (creates Context, Collection, and Project)
- `partner` - Partners (Museums + Institutions)
- `object` - Object items
- `monument` - Monument items
- `monument-detail` - Monument detail items (children of monuments)

### **Phase 2: Images**

Image records with metadata (ItemImages and PartnerImages):

- `object-picture` - Object pictures (ItemImages + child picture Items)
- `monument-picture` - Monument pictures (ItemImages + child picture Items)
- `monument-detail-picture` - Monument detail pictures (ItemImages + child picture Items)
- `partner-picture` - Partner pictures (PartnerImages only)

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

# Legacy Images Root - for image synchronization
LEGACY_IMAGES_ROOT=C:\mwnf-server\pictures\images
```

### Required Environment Variables

| Variable             | Description                     | Default                          |
| -------------------- | ------------------------------- | -------------------------------- |
| `LEGACY_DB_HOST`     | Legacy database hostname        | `localhost`                      |
| `LEGACY_DB_PORT`     | Legacy database port            | `3306`                           |
| `LEGACY_DB_USER`     | Legacy database username        | `root`                           |
| `LEGACY_DB_PASSWORD` | Legacy database password        | (empty)                          |
| `LEGACY_DB_DATABASE` | Legacy database name            | `mwnf3`                          |
| `DB_HOST`            | Target database hostname        | `localhost`                      |
| `DB_PORT`            | Target database port            | `3306`                           |
| `DB_USERNAME`        | Target database username        | `root`                           |
| `DB_PASSWORD`        | Target database password        | (empty)                          |
| `DB_DATABASE`        | Target database name            | `inventory`                      |
| `LEGACY_IMAGES_ROOT` | Root directory of legacy images | `C:\mwnf-server\pictures\images` |

### Validating Database Connections

Before running the import, validate your database connections:

```bash
npx tsx src/cli/import.ts validate
```

This will test both legacy and target database connections and report any issues.

## Data Sources

### Reference Data (Languages & Countries)

Languages and countries are **NOT** imported from the legacy database. Instead, they are loaded from production JSON files:

- `database/seeders/data/languages.json` - ISO 639-3 language codes
- `database/seeders/data/countries.json` - ISO 3166-1 alpha-3 country codes

These files are the same sources used by Laravel seeders and the API importer.

### Legacy Database Data

The following entities are imported from the legacy **mwnf3** database:

- **Projects**: `mwnf3.projects`, `mwnf3.projectnames`
- **Museums**: `mwnf3.museums`, `mwnf3.museumnames`
- **Institutions**: `mwnf3.institutions`, `mwnf3.institutionnames`
- **Objects**: `mwnf3.objects`
- **Monuments**: `mwnf3.monuments`
- **Monument Details**: `mwnf3.monument_details`
- **Object Pictures**: `mwnf3.objects_pictures`
- **Monument Pictures**: `mwnf3.monuments_pictures`
- **Monument Detail Pictures**: `mwnf3.monument_detail_pictures`
- **Museum Pictures**: `mwnf3.museum_pictures`
- **Institution Pictures**: `mwnf3.institution_pictures`

**Note**: The mwnf3 database import is complete. Other legacy databases (travels, sh, thg, etc.) remain to be implemented in future phases.

## Adding New Importers

1. Create legacy type in `domain/types/legacy.ts`
2. Create transformer in `domain/transformers/`
3. Create importer in `importers/phase-XX/`
4. Add to CLI registry in `cli/import.ts`

## Special Transformations

### EPM Context

Items in the EPM project receive special handling:

- **description2** field → becomes a second translation in EPM context
- First translation uses the default context
- Second translation uses the EPM context (identified by backward_compatibility)

### Legacy Country Codes

Special handling for non-standard legacy country codes:

- `ww` → country=null + extra: `{country: "Other"}`
- `fx` → country=null + extra: `{country: "Disputed"}`
- `yu` → country=null + extra: `{country: "Former Yugoslavia"}`
- `px` → country=pse (State of Palestine) + extra: `{country: "Palestinian Territories"}`

### Artist & Tag Extraction

Legacy data contains artists and tags in text fields:

- **Artists**: Extracted, deduplicated, and created as Author entities
- **Tags**: Parsed from delimited strings, categorized (material, keyword, etc.)
- **Backward compatibility**: Maintains links using `mwnf3:artists:{name}` format

### HTML to Markdown

All legacy HTML content is converted to Markdown:

- Preserves basic formatting (bold, italic, links)
- Converts lists, headings, and paragraphs
- Strips unsupported HTML tags
- Handles malformed HTML gracefully

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
✅ **Resilient** - Automatic retry logic for database operations  
✅ **Traceable** - Comprehensive logging to timestamped files  
✅ **Type-Safe** - TypeScript ensures correctness at compile time

## Troubleshooting

### Database Connection Issues

**Error**: `Connection failed`  
**Solution**: Run `npx tsx src/cli/import.ts validate` to test connections. Check `.env` file for correct credentials.

### Import Failures Mid-Process

**Error**: Import stops partway through  
**Solution**: Check the log file in `logs/` directory. The resilient connection should handle temporary issues. For persistent errors, fix the data issue and restart from the failing importer using `--start-at`.

### Duplicate Key Violations

**Error**: `Duplicate entry for key 'backward_compatibility'`  
**Solution**: Wipe the database before re-running. The importer is designed for clean imports, not incremental updates.

### Missing Dependencies

**Error**: Importer complains about missing entities  
**Solution**: Ensure importers run in order. Use `--start-at` and `--stop-at` carefully, respecting dependencies listed in the CLI registry.

### Image Sync Issues

**Error**: Image files not found  
**Solution**: Verify `LEGACY_IMAGES_ROOT` path in `.env` points to the correct legacy images directory.
