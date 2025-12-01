## Context

We now have two distinct importers: index.ts (api based importer) and sql-import-v2.ts (sql based importer), supposedly with identical output; but different input & processing.
We have analysed the possibility to merge them into one single tool - following KISS & DRY principles, and single resposnibility principle, and with an architecture allowing easy extension( e.g. add more importers; covering both approaches: sql or api based)

## Analysis: Merging API and SQL Importers

### Current State

**Two Parallel Hierarchies:**

1. **API Importers** (`/importers/`) - 16 files
   - Use `InventoryApiClient` to make REST calls
   - Tracker: `BackwardCompatibilityTracker` (class-based)
   - Helpers: `ApiArtistHelper`, `ApiAuthorHelper`, `ApiTagHelper`
2. **SQL Importers** (`/sql-importers/`) - 14 files
   - Direct SQL `INSERT` via `mysql2/promise`
   - Tracker: `Map<string, string>` (simple map)
   - Helpers: `ArtistHelper`, `AuthorHelper`, `TagHelper`

**Shared Code:**

- ✅ `HtmlToMarkdownConverter` - identical usage
- ✅ `CodeMappings` - identical
- ✅ `SampleCollector` - identical
- ✅ `BackwardCompatibilityFormatter` - identical
- ✅ Business logic (EPM, tag parsing, field mapping) - **100% duplicated**

### Problems with Current Architecture

1. **Massive Code Duplication** (~8000 lines duplicated)
   - Same field transformations in both Object/ObjectSql importers
   - Same HTML-to-Markdown conversions
   - Same tag parsing logic
   - Same EPM description2 handling

2. **Maintenance Burden**
   - Bug fix requires changes in 2 places
   - Feature addition requires implementation twice
   - Drift risk (they're aligned NOW but won't stay that way)

3. **Violates DRY & KISS**
   - Business logic duplicated
   - Different abstractions for same concept (tracker)
   - Two base classes doing nearly identical things

4. **Poor Extensibility**
   - Adding new entity type = write it twice
   - Can't easily mix strategies (e.g., SQL for bulk, API for validation)

### Proposed Unified Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    BaseImporter (Abstract)                  │
│  - import(): Promise<ImportResult>                          │
│  - transform(legacy: T): Transformed                        │
│  - log(), logWarning(), logError()                          │
└──────────────────────┬──────────────────────────────────────┘
                       │
         ┌─────────────┴─────────────┐
         │                           │
┌────────▼────────┐         ┌───────▼──────────┐
│ ApiStrategy     │         │ SqlStrategy      │
│ - write()       │         │ - write()        │
│ Uses: ApiClient │         │ Uses: Connection │
└─────────────────┘         └──────────────────┘
         │                           │
         └─────────────┬─────────────┘
                       │
              ┌────────▼────────┐
              │ ObjectImporter  │
              │ (one class)     │
              │ - transform()   │
              │ - uses strategy │
              └─────────────────┘
```

### Implementation Strategy

**Phase 1: Extract Business Logic (Non-Breaking)**

```typescript
// NEW: /src/domain/transformers/ObjectTransformer.ts
export class ObjectTransformer {
  static transform(legacyObject: LegacyObject): TransformedObject {
    // ALL the HTML-to-Markdown, EPM logic, field mapping
    // Used by BOTH API and SQL importers
  }
}

// Both importers use it:
const transformed = ObjectTransformer.transform(legacyData);
```

**Phase 2: Unified Strategy Pattern**

```typescript
interface WriteStrategy {
  writeItem(item: ItemData): Promise<string>; // Returns UUID
  writeTranslation(translation: TranslationData): Promise<void>;
}

class ApiWriteStrategy implements WriteStrategy {
  constructor(private client: InventoryApiClient) {}
  async writeItem(item: ItemData): Promise<string> {
    const response = await this.client.item.itemStore(item);
    return response.data.data.id;
  }
}

class SqlWriteStrategy implements WriteStrategy {
  constructor(private db: Connection) {}
  async writeItem(item: ItemData): Promise<string> {
    const id = uuidv4();
    await this.db.execute('INSERT INTO items...', [id, ...]);
    return id;
  }
}
```

**Phase 3: Single Importer Classes**

```typescript
export class ObjectImporter extends BaseImporter {
  constructor(
    private legacyDb: LegacyDatabase,
    private writeStrategy: WriteStrategy,
    private tracker: ITracker // unified interface
  ) {}

  async import(): Promise<ImportResult> {
    const objects = await this.legacyDb.query('SELECT...');

    for (const obj of objects) {
      const transformed = ObjectTransformer.transform(obj);
      const itemId = await this.writeStrategy.writeItem(transformed.item);
      await this.writeStrategy.writeTranslation(transformed.translation);
    }
  }
}
```

### Benefits

✅ **Single Source of Truth** - Business logic in one place
✅ **Easy Testing** - Mock the strategy, test transforms separately  
✅ **Flexible** - Can switch strategies at runtime
✅ **Extensible** - New strategies (e.g., batch API, GraphQL) without touching business logic
✅ **DRY** - ~8000 lines → ~4000 lines
✅ **KISS** - One importer per entity type instead of two
✅ **Single Responsibility** - Transformer transforms, Strategy writes, Importer orchestrates

### Migration Path (Non-Disruptive)

1. **Week 1**: Extract transformers, both importers use them
2. **Week 2**: Create strategy interfaces, API strategy wraps existing
3. **Week 3**: SQL strategy wraps existing
4. **Week 4**: Unified importer classes, deprecate old ones
5. **Week 5**: Remove old importers after validation

### Recommendation

**YES - Merge them**, but do it incrementally:

1. Start with **extracting transformers** (immediate 50% duplication reduction, zero risk)
2. Then **unified tracker interface** (simple abstraction)
3. Finally **strategy pattern** for write operations
4. Keep **both entry points** (sql-import-v2.ts, `index.ts`) - they just instantiate different strategies

This gives you **one codebase** with **two deployment options**, following all SOLID principles and eliminating 4000+ lines of duplication.

## Task

Implement the new architecture proposed in the analysis comprehensiveley (time and complexity are not an issue we want exhaustivity and working solution)
Implement it under a distinct directory (eg: scripts/importer) to avoid breaking the existing and avoid mixing up things.
follow KISS & DRY principles; as well as single resposnibility principle, and make sure the new tool allows for easy extension( e.g. add more importers, covering both approaches: sql and api based)
