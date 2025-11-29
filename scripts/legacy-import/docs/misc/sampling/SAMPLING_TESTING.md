# Testing on data samples to speed up development

## Need:

Running the importers takes several hours; I want to find a way to test it before running it.
Ideally the test should be able to read their data from the sql files at our disposition and to use it for Mocking in tests.
the test should be real test (actually testing the app and its importers; instead of just running detached/independant controls on the data; the test shall be able to take samples that are large enouigh (e.g. 20 records of each type)

What about optionally "collecting" samples whie we run the import operation - fetching a set of successfull records - in various languages - including the default); + collecting records causing warnings to enrish? We could store in in a SQL lite?

## Analysis: Testing Strategy for Import Operations

### Current Challenges

1. **Time**: Full import takes hours - too slow for rapid iteration
2. **No validation**: Can't verify fixes without full import run
3. **Blind development**: Making changes without confidence they work
4. **Regression risk**: New fixes might break existing working code

### Your Proposal: Sample-Based Testing

**Pros:**

- ✅ Fast feedback loop (seconds vs hours)
- ✅ Real data, real edge cases
- ✅ Tests actual importer code paths
- ✅ Can collect diverse samples (languages, warnings, edge cases)
- ✅ SQLite = portable, version-controllable test fixtures

**Cons:**

- ⚠️ Samples might miss some edge cases
- ⚠️ Need mechanism to keep samples updated
- ⚠️ Initial sample collection effort

### Proposed Solution: **Hybrid Approach**

## Implementation Plan

### 1. **Sample Collection During Import** ✅ Best approach

```typescript
// Add to import command
--collect-samples   // Enable sample collection mode
--sample-size=20    // Records per entity type per language
--sample-db=test-fixtures.sqlite  // Output file
```

**What to collect:**

- ✅ **Success cases**: 20 objects per language (eng, fra, deu, ara, ces, etc.)
- ✅ **Warning cases**: All records that trigger warnings (deduplicated)
- ✅ **Edge cases**:
  - Missing fields (name, description)
  - Long fields (>255 chars)
  - Structured tags (contains `:`)
  - Multiple artists (semicolon-separated)
  - Mixed separators (`;` vs `,`)
- ✅ **Diverse languages**: Ensure coverage across all 10 languages
- ✅ **Related entities**: Include dependencies (partners, projects, languages)

**Storage format:**

```sql
-- test-fixtures.sqlite structure
CREATE TABLE legacy_objects (
  project_id TEXT,
  museum_id TEXT,
  number TEXT,
  lang TEXT,
  name TEXT,
  description TEXT,
  -- ... all fields ...
  sample_reason TEXT,  -- 'success', 'warning:missing_name', 'edge:long_field', etc.
  collected_at TIMESTAMP
);

CREATE TABLE legacy_monuments (...);
CREATE TABLE legacy_partners (...);
-- etc.
```

### 2. **Automated Test Suite**

```typescript
// tests/importers/ObjectImporter.integration.test.ts
describe('ObjectImporter with real data samples', () => {
  let testDb: Database;

  beforeAll(() => {
    testDb = loadSampleDatabase('test-fixtures.sqlite');
  });

  test('imports 20 objects successfully across languages', async () => {
    const samples = testDb.query(
      'SELECT * FROM legacy_objects WHERE sample_reason = "success" LIMIT 20'
    );

    for (const obj of samples) {
      const result = await importer.importObject(obj);
      expect(result.success).toBe(true);
      expect(result.warnings).toHaveLength(0);
    }
  });

  test('handles missing name with fallback', async () => {
    const samples = testDb.query(
      'SELECT * FROM legacy_objects WHERE sample_reason LIKE "warning:missing_name%"'
    );

    for (const obj of samples) {
      const result = await importer.importObject(obj);
      expect(result.success).toBe(true);
      expect(result.warnings).toContain('Missing name');
      // Verify fallback was used
      const item = await api.getItem(result.itemId);
      expect(item.name).toMatch(/Object \d+|working_number/);
    }
  });

  test('truncates long alternate_name fields', async () => {
    const samples = testDb.query('SELECT * FROM legacy_objects WHERE LENGTH(name2) > 255');

    for (const obj of samples) {
      const result = await importer.importObject(obj);
      expect(result.success).toBe(true);
      expect(result.warnings).toContain('alternate_name truncated');

      const translation = await api.getItemTranslation(result.itemId, obj.lang);
      expect(translation.alternate_name.length).toBeLessThanOrEqual(255);
      expect(translation.alternate_name).toEndWith('...');
    }
  });

  test('handles structured material tags correctly', async () => {
    const samples = testDb.query('SELECT * FROM legacy_objects WHERE material LIKE "%:%"');

    for (const obj of samples) {
      const result = await importer.importObject(obj);
      expect(result.success).toBe(true);

      // Verify structured data NOT split
      const tags = await api.getItemTags(result.itemId);
      const materialTags = tags.filter((t) => t.category === 'material');

      // Should be single tag, not split
      expect(materialTags).toHaveLength(1);
      expect(materialTags[0].internal_name).toContain('warp');
    }
  });

  test('normalizes tag names to lowercase', async () => {
    const samples = testDb.query(
      'SELECT * FROM legacy_objects WHERE keywords LIKE "%Portrait%" OR keywords LIKE "%PORTRAIT%"'
    );

    for (const obj of samples) {
      const result = await importer.importObject(obj);
      const tags = await api.getItemTags(result.itemId);

      // All tag internal_names should be lowercase
      tags.forEach((tag) => {
        expect(tag.internal_name).toBe(tag.internal_name.toLowerCase());
      });
    }
  });
});
```

### 3. **Sample Collection Implementation**

**Add to BaseImporter:**

```typescript
interface SampleCollector {
  enabled: boolean;
  db: Database;
  sampleSize: number;
  collected: Map<string, Set<string>>; // entityType -> set of IDs
}

class BaseImporter {
  protected sampleCollector?: SampleCollector;

  protected collectSample(
    entityType: string,
    data: any,
    reason: 'success' | 'warning' | 'edge',
    details?: string
  ): void {
    if (!this.sampleCollector?.enabled) return;

    const key = `${entityType}:${reason}`;
    const collected = this.sampleCollector.collected.get(key) || new Set();

    // Limit samples per category
    if (reason === 'success' && collected.size >= this.sampleCollector.sampleSize) {
      return;
    }

    // Always collect warnings and edge cases
    if (reason === 'success' && Math.random() > 0.1) {
      // 10% sampling for success cases to get diversity
      return;
    }

    const recordId = JSON.stringify(data);
    if (!collected.has(recordId)) {
      this.sampleCollector.db.insert(entityType, {
        ...data,
        sample_reason: details ? `${reason}:${details}` : reason,
        collected_at: new Date().toISOString(),
      });
      collected.add(recordId);
      this.sampleCollector.collected.set(key, collected);
    }
  }
}
```

**Update importers to collect samples:**

```typescript
// In ObjectImporter
async importObject(obj: LegacyObject): Promise<boolean> {
  try {
    // ... existing import logic ...

    // Collect sample on success
    this.collectSample('legacy_objects', obj, 'success');

    return true;
  } catch (error) {
    // ... error handling ...
  }
}

// When detecting warnings
if (!name) {
  this.collectSample('legacy_objects', obj, 'warning', 'missing_name');
  // ... fallback logic ...
}

if (alternateName && alternateName.length > 255) {
  this.collectSample('legacy_objects', obj, 'edge', 'long_alternate_name');
  // ... truncation logic ...
}
```

### 4. **CLI Integration**

```powershell
# Collect samples during import (run once to build fixtures)
npx tsx src/index.ts import --collect-samples --sample-size=20 --sample-db=test-fixtures.sqlite

# Run tests with samples
npm run test:integration

# Update samples (re-collect after data changes)
npx tsx src/index.ts collect-samples --update
```

### 5. **Benefits of This Approach**

✅ **Fast iteration**: Tests run in seconds, not hours
✅ **Real data**: Actual edge cases from production legacy DB
✅ **Regression prevention**: Tests catch breaks immediately
✅ **Documentation**: Sample DB shows exactly what data looks like
✅ **Portable**: SQLite fixtures can be committed to repo
✅ **CI-friendly**: Tests run in CI without full legacy DB
✅ **Focused**: Can add specific problematic records to samples
✅ **Maintainable**: Samples auto-update when re-collected

### 6. **Alternative: Direct SQL File Parsing** (More Complex)

Could parse `.sql` files directly for tests:

```typescript
// Read INSERT statements from mwnf3_objects.sql
const sqlContent = fs.readFileSync('.legacy-database/data/mwnf3_objects.sql');
const records = parseSqlInserts(sqlContent);
const samples = selectDiverseSamples(records, 20);
```

**Pros:**

- No need to run import first
- Always up-to-date with SQL files

**Cons:**

- ❌ SQL parsing complexity
- ❌ Need to handle all MySQL syntax variants
- ❌ Harder to select "interesting" samples
- ❌ Still need related entities (partners, projects, etc.)

## Recommendation

**Implement the Sample Collection approach:**

1. **Phase 1** (1-2 hours):
   - Add `SampleCollector` to `BaseImporter`
   - Add `--collect-samples` flag to CLI
   - Implement SQLite storage for samples

2. **Phase 2** (1-2 hours):
   - Run import with sample collection once
   - Build initial test fixtures (~500KB SQLite file)
   - Commit fixtures to repo

3. **Phase 3** (2-3 hours):
   - Write integration tests using samples
   - Cover all data quality issues
   - Add to CI pipeline

4. **Ongoing**:
   - Re-collect samples when legacy DB changes
   - Add specific problematic records manually when found
   - Tests prevent regressions on every PR

**Total effort**: ~6 hours to set up, then fast iteration forever.

Excellent! ✅ **Phase 1 Complete**: Sample collection infrastructure is working.

##

Summary of Phase 1

✅ **Implemented:**

- `SampleCollector` class with SQLite storage
- Integration into `BaseImporter` with `collectSample()` helper
- CLI flags: `--collect-samples`, `--sample-size`, `--sample-db`
- Sample statistics reporting at end of import
- Database initialization with tables for objects, monuments, partners

✅ **Tested:**

- Type checking passes
- Dry run creates database file
- CLI flags work correctly
- Statistics display properly (0 samples since no collection calls yet)
  **Universal Sample Collection System**

✅ **Single table for ALL entity types**: `legacy_samples`

- `entity_type`: language, country, partner, object, monument, etc.
- `source_db`: For multi-database support (mwnf3, discover-islamic-art, etc.)
- `raw_data`: Complete JSON with ALL fields preserved
- `sample_reason`: success, warning:_, edge:_
- `language`: ISO 639-3 code if applicable
- `record_hash`: Deduplication via hash
- Indexes on all key fields

✅ **Infrastructure ready**:

- SampleCollector class
- BaseImporter helper method
- CLI flags working
- Database created successfully

✅ **Tested**:
Create a simple Node script to check the schema:

Ran terminal command:
`node -e "const db = require('better-sqlite3')('E:/inventory/inventory-app/scripts/legacy-import/test-fixtures/samples.sqlite'); console.log(db.prepare('SELECT sql FROM sqlite_master WHERE type=\'table\'').all().map(r => r.sql).join('\n\n'));"`

Perfect! ✅ The universal table structure is correct. Now let's verify Phase 1 is complete and document what we have:

## Phase 1 Complete ✅

**Next: Phase 2 - Add collection calls to all importers**

Let me proceed with Phase 2 by adding sample collection to the importers. I'll start with a simple one to verify the pattern works, then add to all others.

Which importer should I start with to test? Language (simplest) or Object (most complex with warnings/edge cases)?

> add sample collection to all importers in their natural execution order.
> Let me start with the Phase 0 importers (foundation data), then Phase 1.
