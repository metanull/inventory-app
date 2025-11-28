# Legacy Data Import - Development Guide

Running the importer: 
```
Set-Location E:\inventory\inventory-app\scripts\legacy-import
$env:API_EMAIL="user@example.com"
$env:API_PASSWORD="password"
npx tsx src/index.ts import --start-at monument
```

## Overview

This application imports legacy museum database data into the new Inventory Management System via its REST API. It is **NOT** a feature of the main application - it is a standalone, technical utility for one-time data migration.

The application follows our thorough [preliminary analysis](./analysis/PRELIMINARY_ANALYSIS.md)

## Architecture

### Core Components

```
src/
├── index.ts                    # CLI entry point
├── database/
│   └── LegacyDatabase.ts       # MySQL legacy database connector
├── api/
│   └── InventoryApiClient.ts   # API client wrapper (uses published npm package)
├── utils/
│   ├── BackwardCompatibilityFormatter.ts   # Format backward_compatibility field
│   └── BackwardCompatibilityTracker.ts     # Track imported entities
└── importers/
    ├── BaseImporter.ts         # Abstract base class for all importers
    ├── phase-01/               # Phase 1 importers (mwnf3 core)
    ├── phase-02/               # Phase 2 importers (sh, thg)
    └── phase-03/               # Phase 3 importers (travel, explore)
```

### Data Flow

```
Legacy MySQL DB → LegacyDatabase → Importer → Transform → API Client → New Model
                                       ↓
                                BackwardCompatibilityTracker (deduplication)
```

## Core Concepts

### backward_compatibility Field

**Purpose**: Track legacy references to avoid duplicating data across schemas.

**Format**: `{schema}:{table}:{pk_value1}:{pk_value2}:...`

**Rules**:

- Use semicolon (`:`) as separator
- For denormalized tables (language in PK), **EXCLUDE** language column
- For images, **INCLUDE** image index/order number
- Values must be primitive types (string/number)

**Examples**:

```typescript
// Project
'mwnf3:projects:vm';

// Object (denormalized - language excluded)
'mwnf3:objects:vm:ma:louvre:001';

// Image with index
'mwnf3:objects_pictures:vm:ma:louvre:001:1';

// Explore monument (normalized, numeric PK)
'explore:exploremonument:1234';
```

### Denormalization Handling

Legacy mwnf3 and travels schemas store **one entity as multiple rows** with language in PK.

**Example**:

```sql
-- Legacy: TWO rows for ONE object
objects: (vm, ma, louvre, 001, en) - English row
objects: (vm, ma, louvre, 001, fr) - French row
```

**Import Strategy**:

1. **GROUP BY** non-language PK columns
2. Create **ONE Item** per grouped record
3. Create **ONE ItemTranslation** per language row
4. backward_compatibility **EXCLUDES** language: `mwnf3:objects:vm:ma:louvre:001`

**Code Pattern**:

```typescript
// 1. Query with language column
const rows = await db.query('SELECT * FROM mwnf3.objects');

// 2. Group by non-language PK
const grouped = groupBy(rows, (row) =>
  `${row.project_id}:${row.country}:${row.museum_id}:${row.number}`
);

// 3. For each group, create ONE item
for (const [key, languageRows] of grouped) {
  const backwardCompat = BackwardCompatibilityFormatter.formatDenormalized(
    'mwnf3', 'objects', languageRows[0], ['lang']
  );

  // Check if already imported
  if (tracker.exists(backwardCompat)) {
    continue; // Skip duplicate
  }

  // Create item via API
  const item = await api.createItem({ type: 'object', ... });

  // Register in tracker
  tracker.register({
    uuid: item.uuid,
    backwardCompatibility: backwardCompat,
    entityType: 'item',
    createdAt: new Date(),
  });

  // Create translations
  for (const row of languageRows) {
    await api.createItemTranslation({
      item_id: item.uuid,
      language_id: mapLanguage(row.lang), // ISO 2→3 char
      context_id: contextUuid,
      name: row.name,
      // ... other fields
    });
  }
}
```

### Deduplication Strategy

Legacy schemas reference each other. Must avoid creating duplicates.

**Pattern**:

1. **Before creating entity**, format backward_compatibility reference
2. **Check tracker**: `tracker.exists(backwardCompat)`
3. If exists: **Reuse UUID** - `tracker.getUuid(backwardCompat)`
4. If not exists: **Create via API**, then **register** in tracker

**Example - Sharing History referencing mwnf3 object**:

```typescript
// SH schema has reference to mwnf3 object
const mwnf3Ref = BackwardCompatibilityFormatter.format({
  schema: 'mwnf3',
  table: 'objects',
  pkValues: [sh_row.project_id, sh_row.country, sh_row.museum_id, sh_row.number],
});

if (tracker.exists(mwnf3Ref)) {
  // Object already imported from mwnf3
  const itemUuid = tracker.getUuid(mwnf3Ref);
  // Use existing UUID, create contextual translation only
} else {
  // New object, create it
}
```

### Image Import Strategy

**Problem**: Legacy has two image models:

1. **Ordered lists** (mwnf3, sh, travels, explore): Multiple images per item
2. **Single selections** (thg galleries): One image from an item with contextualized caption

**Solution**: Tree-structured items with type='picture'

**Rules**:

1. **Primary Image**: Image #1 → ItemImage directly on parent item

   ```typescript
   // Copy file from legacy network share
   const filePath = await copyImageFile(legacyPath);

   // Create ItemImage on parent
   await api.createItemImage({
     item_id: parentItemUuid,
     file_path: filePath,
     order: 1,
     backward_compatibility: formatImage('mwnf3', 'objects_pictures', pkValues, 1),
   });
   ```

2. **All Images**: Create child Item type='picture' for EACH image (including #1)

   ```typescript
   for (const [index, imageRow] of images.entries()) {
     // Create picture item
     const pictureItem = await api.createItem({
       type: 'picture',
       parent_id: parentItemUuid,
       backward_compatibility: formatImage(schema, table, pkValues, index + 1),
     });

     // Create ItemImage for picture (same file, no duplication)
     await api.createItemImage({
       item_id: pictureItem.uuid,
       file_path: filePath, // Same file as parent's ItemImage
       order: index + 1,
     });

     // Create translations with captions
     for (const lang of languages) {
       await api.createItemTranslation({
         item_id: pictureItem.uuid,
         language_id: lang,
         context_id: defaultContext,
         name: imageRow[`caption_${lang}`],
       });
     }
   }
   ```

3. **Contextual Captions** (thg galleries): Add ItemTranslation with gallery context_id

   ```typescript
   // Picture item already exists from Rule 2
   const pictureUuid = tracker.getUuid(pictureBackwardCompat);

   // Add translation in gallery context
   await api.createItemTranslation({
     item_id: pictureUuid,
     language_id: 'eng',
     context_id: galleryCollectionUuid, // Gallery-specific context
     name: gallery_row.caption_en, // Contextualized caption
   });
   ```

**Result**: Each image exists as:

- ItemImage on parent (primary image only)
- Child Item type='picture' with ItemImage (all images)
- Translations in multiple contexts (default + gallery-specific)

### Collection Hierarchies

Legacy schemas have multi-level collection structures.

**Mapping**:

```typescript
// Legacy: Exhibition → Theme → Subtheme
// New Model: Collection → Collection → Collection (with parent_id)

const exhibition = await api.createCollection({
  name: 'Exhibition Name',
  type: 'exhibition',
  parent_id: null, // Top-level
});

const theme = await api.createCollection({
  name: 'Theme Name',
  type: 'theme',
  parent_id: exhibition.uuid, // Child of exhibition
});

const subtheme = await api.createCollection({
  name: 'Subtheme Name',
  type: 'theme',
  parent_id: theme.uuid, // Child of theme
});
```

**Collection Types**:

- `collection` - Generic collection (default)
- `exhibition` - Curated exhibition
- `exhibition_trail` - Travel trail
- `gallery` - Thematic gallery
- `theme` - Theme within exhibition/gallery
- `itinerary` - Travel itinerary
- `location` - Geographic location

**Import Order**: ALWAYS import parents before children.

## Adding New Importers

### TDD Workflow

**ALWAYS write tests FIRST, then implement.**

1. **RED**: Write failing test
2. **GREEN**: Implement importer to pass test
3. **REFACTOR**: Clean up code

### Step-by-Step Guide

#### 1. Create Test File

```bash
# Determine phase (see docs/legacy-import/LEGACY_DATA_IMPORT_TASK_PLAN.md)
# Create test in appropriate phase directory
touch tests/integration/phase-01-mwnf3-core/MyEntityImporter.test.ts
```

#### 2. Write Test (RED)

```typescript
import { describe, it, expect, beforeEach, vi } from 'vitest';
import { MyEntityImporter } from '../../../src/importers/phase-01/MyEntityImporter.js';
import { BackwardCompatibilityTracker } from '../../../src/utils/BackwardCompatibilityTracker.js';

describe('MyEntityImporter', () => {
  let importer: MyEntityImporter;
  let mockDb: any;
  let mockApi: any;
  let tracker: BackwardCompatibilityTracker;

  beforeEach(() => {
    // Mock database
    mockDb = {
      query: vi.fn(),
      queryOne: vi.fn(),
    };

    // Mock API client
    mockApi = {
      createMyEntity: vi.fn(),
    };

    tracker = new BackwardCompatibilityTracker();

    importer = new MyEntityImporter({
      legacyDb: mockDb,
      apiClient: mockApi,
      tracker,
      dryRun: false,
      limit: 0,
    });
  });

  it('should import entity from legacy database', async () => {
    // Arrange: Mock legacy data
    mockDb.query.mockResolvedValue([{ id: 1, name_en: 'Entity EN', name_fr: 'Entity FR' }]);
    mockApi.createMyEntity.mockResolvedValue({ uuid: 'test-uuid' });

    // Act: Run import
    const result = await importer.import();

    // Assert: Verify API calls and results
    expect(mockApi.createMyEntity).toHaveBeenCalledWith({
      name: 'Entity EN',
      backward_compatibility: 'mwnf3:my_table:1',
    });
    expect(result.imported).toBe(1);
    expect(tracker.exists('mwnf3:my_table:1')).toBe(true);
  });

  it('should skip duplicate entities', async () => {
    // Arrange: Register entity in tracker
    tracker.register({
      uuid: 'existing-uuid',
      backwardCompatibility: 'mwnf3:my_table:1',
      entityType: 'item',
      createdAt: new Date(),
    });
    mockDb.query.mockResolvedValue([{ id: 1, name_en: 'Entity' }]);

    // Act
    const result = await importer.import();

    // Assert: Should not create, should skip
    expect(mockApi.createMyEntity).not.toHaveBeenCalled();
    expect(result.skipped).toBe(1);
  });

  it('should handle denormalized data', async () => {
    // Test grouping by non-language PK
    mockDb.query.mockResolvedValue([
      { id: 1, lang: 'en', name: 'Name EN' },
      { id: 1, lang: 'fr', name: 'Name FR' },
    ]);

    const result = await importer.import();

    // Should create ONE entity, TWO translations
    expect(mockApi.createMyEntity).toHaveBeenCalledTimes(1);
    expect(result.imported).toBe(1);
  });
});
```

**Run test (should FAIL)**:

```bash
npm test tests/integration/phase-01-mwnf3-core/MyEntityImporter.test.ts
```

#### 3. Implement Importer (GREEN)

```typescript
// src/importers/phase-01/MyEntityImporter.ts
import { BaseImporter, ImportResult, ImportContext } from '../BaseImporter.js';
import { BackwardCompatibilityFormatter } from '../../utils/BackwardCompatibilityFormatter.js';

export class MyEntityImporter extends BaseImporter {
  constructor(context: ImportContext) {
    super(context);
  }

  getName(): string {
    return 'MyEntityImporter';
  }

  async import(): Promise<ImportResult> {
    this.log('Starting import...');

    try {
      // 1. Query legacy database
      const rows = await this.context.legacyDb.query<LegacyRow>('SELECT * FROM mwnf3.my_table');

      let imported = 0;
      let skipped = 0;
      const errors: string[] = [];

      // 2. Group by non-language PK (if denormalized)
      const grouped = this.groupByPrimaryKey(rows);

      // 3. Process each group
      for (const [key, languageRows] of grouped) {
        try {
          // 4. Format backward_compatibility (exclude language)
          const backwardCompat = BackwardCompatibilityFormatter.formatDenormalized(
            'mwnf3',
            'my_table',
            languageRows[0],
            ['lang']
          );

          // 5. Check for duplicates
          if (this.context.tracker.exists(backwardCompat)) {
            this.log(`Skipping duplicate: ${backwardCompat}`);
            skipped++;
            continue;
          }

          // 6. Create entity via API
          if (!this.context.dryRun) {
            const entity = await this.context.apiClient.createMyEntity({
              // Map fields
              name: languageRows[0].name,
              backward_compatibility: backwardCompat,
            });

            // 7. Register in tracker
            this.context.tracker.register({
              uuid: entity.uuid,
              backwardCompatibility: backwardCompat,
              entityType: 'item',
              createdAt: new Date(),
            });

            // 8. Create translations
            for (const row of languageRows) {
              await this.context.apiClient.createTranslation({
                item_id: entity.uuid,
                language_id: this.mapLanguage(row.lang),
                name: row.name,
              });
            }
          }

          imported++;
        } catch (error) {
          this.logError(`Failed to import ${key}`, error);
          errors.push(`${key}: ${error}`);
        }
      }

      this.log(`Import complete. Imported: ${imported}, Skipped: ${skipped}`);
      return this.createResult(true, imported, skipped, errors);
    } catch (error) {
      this.logError('Import failed', error);
      return this.createResult(false, 0, 0, [String(error)]);
    }
  }

  private groupByPrimaryKey(rows: LegacyRow[]): Map<string, LegacyRow[]> {
    const grouped = new Map<string, LegacyRow[]>();
    for (const row of rows) {
      const key = `${row.id}`; // Or multi-column key
      if (!grouped.has(key)) {
        grouped.set(key, []);
      }
      grouped.get(key)!.push(row);
    }
    return grouped;
  }

  private mapLanguage(legacyCode: string): string {
    // ISO 2-char → 3-char mapping
    const map: Record<string, string> = {
      en: 'eng',
      fr: 'fra',
      ar: 'ara',
      es: 'spa',
    };
    return map[legacyCode] || legacyCode;
  }
}

interface LegacyRow {
  id: number;
  lang: string;
  name: string;
  // ... other fields
}
```

**Run test (should PASS)**:

```bash
npm test tests/integration/phase-01-mwnf3-core/MyEntityImporter.test.ts
```

#### 4. Refactor (REFACTOR)

- Extract common patterns to utility functions
- Simplify complex logic
- Add JSDoc comments
- Ensure no TypeScript errors

## Testing Requirements

### Unit Tests

- Test utility functions in isolation
- Mock all external dependencies
- Fast execution (no database/API calls)

### Integration Tests

- Test importer classes with mocked database/API
- Verify backward_compatibility tracking
- Test error handling

### E2E Tests

- Require actual test database
- Run full import phases
- Validate data integrity

### Coverage Requirements

- **Minimum**: 80% line coverage for utilities
- **Required**: 100% coverage for BackwardCompatibilityFormatter and Tracker

## Development Commands

```bash
# Install dependencies
cd scripts/legacy-import
npm install

# Run tests
npm test                  # Run all tests
npm run test:watch        # Watch mode (TDD)
npm run test:coverage     # With coverage report

# Type checking
npm run type-check        # TypeScript compilation check

# Linting
npm run lint              # Fix linting issues
npm run lint:check        # Check only

# Build
npm run build             # Compile TypeScript to dist/

# Run application
npm run dev               # Development mode (watch)
npm start                 # Production mode
```

## Importing Legacy Data

```bash
# Authentication
npx tsx src/index.ts login                    # Login and get API token
npx tsx src/index.ts validate                 # Test connections

npx tsx src/index.ts login --url http://api   # Login to custom API URL

# Import commands
npx tsx src/index.ts import                   # Run all importes

# Custom import options
npx tsx src/index.ts import --list-importers  # List importers
npm run import:list                           # List importers (shorthand)
npx tsx src/index.ts import --phase 0 --start-at country        # Resume at phase 0:country
npx tsx src/index.ts import --phase 1 --start-at partner        # Resume at phase 1:partner
npx tsx src/index.ts import --phase 1 --only monument           # Import only monuments
npx tsx src/index.ts import --phase 1 --only monument --dry-run # Pretend/ don't actually import

### Enable Verbose Logging
LOG_LEVEL=debug npx tsx src/index.ts import --phase 1
```

## Configuration

### Initial Setup

1. Copy `.env.example` to `.env`:

   ```bash
   cp .env.example .env
   ```

2. Edit `.env` with your legacy database credentials:

   ```bash
   LEGACY_DB_HOST=localhost
   LEGACY_DB_PORT=3306
   LEGACY_DB_USER=root
   LEGACY_DB_PASSWORD=your_password

   API_BASE_URL=http://localhost:8000/api
   ```

### Full Configuration

```bash
# Legacy database (MySQL)
LEGACY_DB_HOST=localhost
LEGACY_DB_PORT=3306
LEGACY_DB_USER=root
LEGACY_DB_PASSWORD=your_password

# New API
API_BASE_URL=http://localhost:8000/api

# Import options
DRY_RUN=true              # Simulate without writing
BATCH_SIZE=50             # Records per batch
LOG_LEVEL=info            # Logging level

# Legacy images (Windows UNC path)
LEGACY_IMAGE_PATH=\\\\virtual-office.museumwnf.org\\C$\\mwnf-server\\pictures\\images
```

### TypeScript Source Maps

Source maps are enabled. Stack traces show original TypeScript line numbers.

## Common Pitfalls

### ❌ Don't: Hardcode UUIDs

```typescript
const contextUuid = 'abc-123'; // Wrong - UUID changes per environment
```

✅ **Do**: Use tracker to get UUIDs

```typescript
const contextUuid = tracker.getUuid('mwnf3:projects:vm');
```

### ❌ Don't: Include language in backward_compatibility

```typescript
// Wrong for denormalized tables
const ref = `mwnf3:objects:vm:ma:louvre:001:en`;
```

✅ **Do**: Exclude language column

```typescript
const ref = BackwardCompatibilityFormatter.formatDenormalized('mwnf3', 'objects', row, ['lang']);
// Result: mwnf3:objects:vm:ma:louvre:001
```

### ❌ Don't: Create duplicate entities

```typescript
// Wrong - always creates new entity
await api.createItem({ ... });
```

✅ **Do**: Check tracker first

```typescript
if (tracker.exists(backwardCompat)) {
  const uuid = tracker.getUuid(backwardCompat);
  // Use existing
} else {
  const item = await api.createItem({ ... });
  tracker.register({ uuid: item.uuid, backwardCompatibility: backwardCompat, ... });
}
```

### ❌ Don't: Import images without deduplication

```typescript
// Wrong - uploads same file multiple times
for (const format of ['small', 'thumb', 'large']) {
  await uploadImage(format);
}
```

✅ **Do**: Upload original only, track by hash

```typescript
const originalPath = stripFormatFromPath(legacyPath);
if (!alreadyUploaded(originalPath)) {
  await uploadImage(originalPath);
}
```

## References

- **Analysis Documents**: `/docs/legacy-import/`
  - `MASTER-Import-Strategy-And-Mapping.md` - Entity mappings, hierarchies
  - `LEGACY_DATA_IMPORT_TASK_PLAN.md` - Phases and tasks
- **Test Structure**: `/tests/README.md`
- **Main App API**: `http://localhost:8000/docs/api` (when running)
