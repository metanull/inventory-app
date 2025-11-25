# Quick Start: Running Sample-Based Tests

## Prerequisites

Make sure you have:
- ✅ Collected samples in `test-fixtures/samples.sqlite` (run import with `--collect-samples`)
- ✅ Installed dependencies: `npm ci --no-audit --no-fund`

## Running Tests

### All Tests
```bash
cd scripts/legacy-import
npm test
```

### Specific Test File
```bash
# Object importer tests
npm test ObjectImporter.samples.test

# Monument importer tests
npm test MonumentImporter.samples.test

# All Phase 0 foundation tests
npm test phase-00

# All Phase 1 core tests
npm test phase-01
```

### Watch Mode (for development)
```bash
npm test -- --watch
```

### With Coverage
```bash
npm test -- --coverage
```

## Understanding Test Output

### Success
```
✓ ObjectImporter - Sample-Based Tests > import with success samples > should import object samples successfully (15ms)
  Imported 10 objects with 2 warnings
  Created 8 items and 10 translations
```

### Data Quality Issues Found
```
✓ ObjectImporter - Sample-Based Tests > data quality handling > should handle missing names
  Testing 3 objects with missing names
  Fallback name used: Object 12345
  Fallback name used: Working Number ABC-001
```

### Failure (needs fixing)
```
✗ ObjectImporter - Sample-Based Tests > should create items with correct structure
  Expected: type to be 'object'
  Received: undefined
```

## Common Test Scenarios

### 1. Testing After Code Changes
```bash
# Make changes to ObjectImporter.ts
# Run tests to validate
npm test ObjectImporter.samples.test

# If tests pass, commit changes
git add .
git commit -m "fix: handle missing object names with fallback"
```

### 2. Investigating Data Quality Issues
```bash
# Run specific data quality tests
npm test "missing names"

# Review warnings and fix in importer
# Re-run to verify fix
npm test "missing names"
```

### 3. Debugging a Specific Issue
```typescript
// In test file, add focus
it.only('should handle specific edge case', async () => {
  // Test code...
});
```

```bash
# Run only focused tests
npm test -- --reporter=verbose
```

## Interpreting Sample Statistics

Tests show sample statistics like:
```
Object sample statistics: {
  'object:success': 120,
  'object:warning:missing_name': 8,
  'object:warning:missing_description': 15,
  'object:edge:long_field': 3
}
```

**What this means**:
- 120 normal/valid object samples
- 8 objects with missing names (fallback logic needed)
- 15 objects with missing descriptions (fallback applied)
- 3 objects with edge cases (e.g., very long fields)

## Test Failures: What to Do

### Type Errors
```
TypeError: Cannot read property 'id' of undefined
```
**Fix**: Check if the dependency (partner, project, etc.) is registered in tracker during `beforeEach()`.

### Assertion Failures
```
Expected: 10 items created
Received: 5 items created
```
**Fix**: Check if the importer is correctly grouping denormalized records (for Object/Monument).

### API Call Mismatches
```
Expected: itemStore called 10 times
Received: itemStore called 5 times
```
**Fix**: Verify the test is loading the correct number of samples and that grouping logic is correct.

## Adding New Tests

### 1. For New Data Quality Issue
```typescript
it('should handle new edge case', async () => {
  // Load samples with the issue
  const samples = helper.loadSamples('object', { 
    reason: 'edge', 
    edgeType: 'special_characters' 
  });
  
  if (samples.length > 0) {
    const mockContext = helper.createMockContextWithQueries([samples]);
    const importer = new ObjectImporter(mockContext);
    const result = await importer.import();
    
    expect(result.success).toBe(true);
    // Add specific assertions...
  }
});
```

### 2. For New Importer
```typescript
import { describe, it, expect, beforeAll, afterAll } from 'vitest';
import { SampleBasedTestHelper } from '../../helpers/SampleBasedTestHelper.js';
import { NewImporter } from '../../../src/importers/phase-01/NewImporter.js';

describe('NewImporter - Sample-Based Tests', () => {
  let helper: SampleBasedTestHelper;

  beforeAll(() => {
    helper = new SampleBasedTestHelper();
  });

  afterAll(() => {
    helper.close();
  });

  it('should import samples successfully', async () => {
    const mockContext = helper.createMockContext('new_entity', 10);
    const importer = new NewImporter(mockContext);
    const result = await importer.import();
    
    expect(result.success).toBe(true);
  });
});
```

## Collecting Fresh Samples

If you need to update samples after legacy data changes:

```bash
# Collect new samples during import
npm run import -- --collect-samples --sample-size=20

# This updates test-fixtures/samples.sqlite
# Commit the updated file
git add test-fixtures/samples.sqlite
git commit -m "chore: update test samples"
```

## Tips for Effective Testing

### 1. Start Small
- Run one test file at a time
- Fix issues one by one
- Gradually expand coverage

### 2. Use Console Logs
```typescript
it('should debug issue', async () => {
  const samples = helper.loadSamples('object', { limit: 1 });
  console.log('Sample data:', JSON.stringify(samples[0], null, 2));
  // Continue with test...
});
```

### 3. Verify Sample Data
```typescript
it('should check sample quality', () => {
  const stats = helper.getReader().getStats();
  console.log('Available samples:', stats);
  
  // Verify you have the samples you need
  expect(stats['object:success']).toBeGreaterThan(0);
});
```

### 4. Test Dependencies First
- Make sure Phase 0 tests pass before Phase 1
- Phase 0: Languages, Countries
- Phase 1: Projects, Partners, Items

## Next Steps

1. **Run all tests**: `npm test`
2. **Review failures**: Fix issues in importer code
3. **Re-run tests**: Validate fixes
4. **Full import**: When ready, run full import to validate

## Getting Help

- Check test output for specific error messages
- Review `tests/README.md` for detailed testing guide
- Look at existing test files for patterns
- Check `docs/SAMPLE_TESTING_IMPLEMENTATION.md` for architecture

---

**Remember**: Tests should run fast (5-10 seconds total). If they're slow, check sample limits.
