# Sample-Based Integration Tests

This directory contains comprehensive integration tests for legacy data importers using **real data samples** collected from the legacy database and stored in SQLite.

## Overview

Instead of testing against the full legacy database (which takes hours), these tests use a representative sample of real data stored in `/test-fixtures/samples.sqlite`. This approach provides:

- ✅ **Fast feedback**: Tests run in seconds instead of hours
- ✅ **Real data**: Actual edge cases, data quality issues, and patterns from production
- ✅ **Comprehensive coverage**: Success cases, warnings, edge cases, all languages
- ✅ **Reliable**: Deterministic results, no external dependencies
- ✅ **Continuous validation**: Can run on every commit

## Sample Collection

Samples are collected during actual import runs using the `SampleCollector` utility:

```typescript
// Collect diverse samples during import
collectSample(
  'object', // Entity type
  legacyRecord, // Raw legacy data (all fields preserved)
  'success', // Reason: 'success', 'warning', 'edge'
  'missing_name', // Optional detail
  'eng', // Language if applicable
  'mwnf3' // Source database
);
```

### Sample Categories

- **Success samples**: Normal, valid records (20 per entity type, per language)
- **Warning samples**: Records that trigger warnings (all collected)
- **Edge cases**: Unusual patterns (long fields, special characters, etc.)
- **Foundation data**: ALL languages and countries (needed for dependencies)

## Test Structure

### Test Organization

```
tests/
├── helpers/
│   └── SampleBasedTestHelper.ts    # Test utilities
├── unit/                           # Unit tests for utilities
│   ├── BackwardCompatibilityFormatter.test.ts
│   ├── BackwardCompatibilityTracker.test.ts
│   ├── LoginHelper.test.ts
│   └── SampleReader.test.ts
├── integration/
│   ├── phase-00/                    # Foundation data (languages, countries)
│   │   ├── LanguageImporter.samples.test.ts
│   │   ├── LanguageTranslationImporter.samples.test.ts
│   │   ├── CountryImporter.samples.test.ts
│   │   └── CountryTranslationImporter.samples.test.ts
│   ├── phase-01/                    # Core entities (projects, partners, items)
│   │   ├── ProjectImporter.samples.test.ts
│   │   ├── PartnerImporter.test.ts (aggregates Museum + Institution)
│   │   ├── ObjectImporter.samples.test.ts
│   │   └── MonumentImporter.samples.test.ts
│   ├── phase-02-sh-thg/            # Phase 2: Future
│   ├── phase-03-travel-explore/    # Phase 3: Future
│   └── phase-04-other-schemas/     # Phase 4: Future
└── e2e/                            # End-to-end tests
    ├── full-import.test.ts
    └── validation.test.ts
```

### Test Helper: `SampleBasedTestHelper`

The `SampleBasedTestHelper` class provides utilities for sample-based testing:

```typescript
const helper = new SampleBasedTestHelper();

// Load samples with filtering
const successSamples = helper.loadSamples('object', { reason: 'success', limit: 20 });
const warningSamples = helper.loadSamples('object', {
  reason: 'warning',
  warningType: 'missing_name',
});
const frenchSamples = helper.loadSamples('object', { language: 'fra', limit: 10 });

// Create mock context with sample data
const mockContext = helper.createMockContext('object', 10);

// Setup foundation data (languages, countries) in tracker
helper.setupFoundationData();

// Verify API calls
helper.verifyApiCalls(mockContext.apiClient, 'item.itemStore', 10);
const calls = helper.getApiCalls(mockContext.apiClient, 'itemTranslation.itemTranslationStore');
```

## Running Tests

```bash
# Run all sample-based tests
npm test

# Run specific test file
npm test ObjectImporter.samples.test

# Run with coverage
npm test -- --coverage

# Watch mode for development
npm test -- --watch
```

## Test Data

Test fixtures are stored in `test-fixtures/`:

- `samples.sqlite`: Real data samples collected during import (committed to repo)
