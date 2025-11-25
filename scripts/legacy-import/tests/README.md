# Test Structure

This directory contains tests organized to match the import phases from the Task Plan.

## Structure

```
tests/
├── unit/                           # Unit tests for utilities and helpers
│   ├── BackwardCompatibilityFormatter.test.ts
│   ├── BackwardCompatibilityTracker.test.ts
│   └── utils/                      # Additional utility tests
│
├── integration/                    # Integration tests for importers
│   ├── phase-01-mwnf3-core/       # Phase 1: mwnf3 base schema
│   │   ├── ProjectImporter.test.ts
│   │   ├── PartnerImporter.test.ts
│   │   ├── ItemImporter.test.ts
│   │   └── ImageImporter.test.ts
│   │
│   ├── phase-02-sh-thg/           # Phase 2: Sharing History & Thematic Gallery
│   │   ├── ShExhibitionImporter.test.ts
│   │   ├── ThgGalleryImporter.test.ts
│   │   └── ContextualTranslationImporter.test.ts
│   │
│   ├── phase-03-travel-explore/   # Phase 3: Travel & Explore schemas
│   │   ├── TravelTrailImporter.test.ts
│   │   ├── ExploreMonumentImporter.test.ts
│   │   └── ExploreItineraryImporter.test.ts
│   │
│   └── phase-04-other-schemas/    # Phase 4: Remaining schemas
│
└── e2e/                           # End-to-end tests
    ├── full-import.test.ts        # Complete import run
    └── validation.test.ts         # Post-import validation

```

## Testing Approach

### TDD Workflow

1. Write test first (RED)
2. Implement importer (GREEN)
3. Refactor (REFACTOR)

### Test Categories

**Unit Tests**: Test individual functions in isolation

- Backward compatibility formatting
- Data transformation functions
- PK mapping utilities

**Integration Tests**: Test importers with mocked database/API

- One test file per importer class
- Mock legacy database responses
- Mock API client calls
- Verify backward_compatibility tracking

**E2E Tests**: Test full import process

- Require actual test database
- Run complete import phases
- Validate data integrity
- Check for duplicates

## Running Tests

```bash
# Run all tests
npm test

# Run specific phase
npm test -- tests/integration/phase-01-mwnf3-core

# Run in watch mode
npm run test:watch

# Run with coverage
npm run test:coverage
```

## Test Data

Test fixtures are stored in `tests/fixtures/`:

- `legacy-data/`: Sample legacy database records
- `api-responses/`: Sample API responses
- `expected-results/`: Expected import outcomes
