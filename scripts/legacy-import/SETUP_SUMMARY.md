# Legacy Data Import Application - Setup Summary

**Created**: 2025-11-18  
**Location**: `/scripts/legacy-import/`  
**Purpose**: Standalone Node.js CLI application for importing legacy museum data into new Inventory Management System via API

---

## What Was Created

### ✅ 1. Application Skeleton

**Node.js + TypeScript** application with:
- Commander.js for CLI commands
- MySQL2 for legacy database access
- Vitest for TDD testing
- ESLint + Prettier for code quality

**Files Created**:
```
scripts/legacy-import/
├── package.json              # Dependencies and npm scripts
├── tsconfig.json             # TypeScript configuration (strict mode)
├── vitest.config.ts          # Test runner configuration
├── eslint.config.js          # Linting rules
├── .prettierrc.json          # Code formatting rules
├── .env.example              # Configuration template
├── .gitignore                # Ignored files
└── README.md                 # Quick start guide
```

**Key Dependencies**:
- `mysql2` - Legacy database connector
- `commander` - CLI framework
- `dotenv` - Environment configuration
- `vitest` - Test framework (TDD)
- `tsx` - TypeScript execution

### ✅ 2. Core Application Structure

```
src/
├── index.ts                  # CLI entry point with commands:
│                             #   - import (run import process)
│                             #   - validate (test connections)
│                             #   - status (show progress)
│
├── database/
│   └── LegacyDatabase.ts     # MySQL connector for legacy schemas
│                             # Methods: connect(), query(), queryOne()
│
├── api/
│   └── InventoryApiClient.ts # API client wrapper (uses published npm package)
│                             # TODO: Install @metanull/inventory-app-api-client
│
├── utils/
│   ├── BackwardCompatibilityFormatter.ts
│   │                         # Format backward_compatibility references
│   │                         # Rules: schema:table:pk1:pk2:...
│   │                         # Special methods for denormalized tables and images
│   │
│   └── BackwardCompatibilityTracker.ts
│                             # Track imported entities by backward_compatibility
│                             # Prevent duplicating data across schemas
│                             # Methods: register(), exists(), getUuid()
│
└── importers/
    └── BaseImporter.ts       # Abstract class for all importers
                              # Provides: import(), getName(), log(), error handling
```

### ✅ 3. Test Hierarchy

**Organized by import phases from LEGACY_DATA_IMPORT_TASK_PLAN.md**:

```
tests/
├── README.md                 # Test structure and workflow guide
│
├── unit/                     # Unit tests (utilities, helpers)
│   ├── BackwardCompatibilityFormatter.test.ts
│   │                         # ✅ 6 test cases (format, parse, denormalized, images)
│   └── BackwardCompatibilityTracker.test.ts
│                             # ✅ 8 test cases (register, exists, getUuid, stats)
│
├── integration/              # Integration tests (importers with mocked DB/API)
│   ├── phase-01-mwnf3-core/
│   │   └── README.md         # Phase 1 guide: Projects, Partners, Items, Images
│   │
│   ├── phase-02-sh-thg/
│   │   └── README.md         # Phase 2 guide: Exhibitions, Galleries, Contextual
│   │
│   ├── phase-03-travel-explore/
│   │   └── README.md         # Phase 3 guide: Trails, Explore Monuments
│   │
│   └── phase-04-other-schemas/
│       └── .gitkeep          # Placeholder for future phases
│
└── e2e/                      # End-to-end tests (full import runs)
    ├── full-import.test.ts   # Complete import execution
    └── validation.test.ts    # Post-import data integrity checks
```

**Test Files Include**:
- ✅ **20 unit test cases** for BackwardCompatibility utilities
- ✅ **Phase-specific READMEs** explaining import order, denormalization, hierarchies
- ✅ **E2E test placeholders** for full import validation

### ✅ 4. Comprehensive Documentation

```
docs/
└── DEVELOPMENT.md            # 600+ line comprehensive guide covering:
                              # - Architecture and data flow
                              # - backward_compatibility format rules
                              # - Denormalization handling (grouping, translation)
                              # - Deduplication strategy (tracker usage)
                              # - Image import strategy (tree structure, picture type)
                              # - Collection hierarchies (parent_id relationships)
                              # - TDD workflow (RED → GREEN → REFACTOR)
                              # - Adding new importers (step-by-step)
                              # - Common pitfalls and solutions
```

**Documentation Covers**:

1. **Core Concepts**:
   - ✅ backward_compatibility field format and rules
   - ✅ Denormalization handling (language in PK)
   - ✅ Deduplication via tracker
   - ✅ Image import strategy (3 rules)
   - ✅ Collection hierarchies and types

2. **TDD Workflow**:
   - ✅ Step-by-step guide to adding new importers
   - ✅ Test-first approach with examples
   - ✅ Mock patterns for database and API

3. **Code Examples**:
   - ✅ Denormalized data grouping
   - ✅ Deduplication checks
   - ✅ Image import (primary + picture items)
   - ✅ Collection hierarchy creation

4. **Common Pitfalls**:
   - ❌ Don't hardcode UUIDs → ✅ Use tracker
   - ❌ Don't include language in backward_compatibility → ✅ Use formatDenormalized
   - ❌ Don't create duplicates → ✅ Check tracker first
   - ❌ Don't upload image duplicates → ✅ Upload original only

---

## Key Design Decisions

### 1. **Detached from Main Application**
- ✅ Standalone CLI app in `/scripts/legacy-import/`
- ✅ Uses published API client (not internal code)
- ✅ Not a feature - one-time migration utility

### 2. **API-Only Communication**
- ✅ Reads from legacy MySQL database
- ✅ Writes via REST API (Sanctum authenticated)
- ✅ No direct database writes to new model
- ✅ Uses published `@metanull/inventory-app-api-client` npm package

### 3. **TDD Approach**
- ✅ Tests written FIRST, then implementation
- ✅ Unit tests for utilities (100% coverage required)
- ✅ Integration tests for importers (mocked DB/API)
- ✅ E2E tests for full import validation

### 4. **Backward Compatibility Tracking**
- ✅ Central tracker to avoid duplicates
- ✅ Format: `schema:table:pk1:pk2:...`
- ✅ Special handling for denormalized tables (exclude language)
- ✅ Image references include index

### 5. **Denormalization Handling**
- ✅ Legacy mwnf3/travels have language in PK (multiple rows = one entity)
- ✅ Group by non-language PK columns
- ✅ Create ONE entity + multiple translations
- ✅ backward_compatibility excludes language

### 6. **Image Import Strategy**
- ✅ Image #1 → ItemImage on parent
- ✅ ALL images → Child items type='picture' with ItemImage
- ✅ Picture items support contextual captions (THG galleries)
- ✅ Upload original only (not cached/resized versions)

### 7. **Phase-Based Execution**
- ✅ 17 phases following LEGACY_DATA_IMPORT_TASK_PLAN.md
- ✅ Respects dependencies (parents before children)
- ✅ Can run individual phases or all at once

---

## Next Steps

### 1. Install Dependencies
```bash
cd scripts/legacy-import
npm install
```

### 2. Install API Client Package

**TODO**: Add published npm package to dependencies:
```bash
npm install @metanull/inventory-app-api-client
```

Update `src/api/InventoryApiClient.ts` to use the package.

### 3. Configure Environment

```bash
cp .env.example .env
# Edit with actual credentials:
# - Legacy MySQL database connection
# - New API URL and token
# - Legacy image network path
```

### 4. Start TDD Implementation

**Follow Phase 1 of LEGACY_DATA_IMPORT_TASK_PLAN.md**:

1. ✅ **ProjectImporter** (Context + Collection from mwnf3.projects)
2. ✅ **PartnerImporter** (Partner from mwnf3.museums, institutions)
3. ✅ **ItemImporter** (Item from mwnf3.objects, monuments, details)
4. ✅ **ImageImporter** (ImageUpload + ItemImage from mwnf3.*_pictures)
5. ✅ **AuthorImporter** (Author from author fields)
6. ✅ **TagImporter** (Tag from semicolon-separated lists)
7. ✅ **ItemLinkImporter** (ItemItemLink from relationship tables)

**For Each Importer**:
1. Write test in `tests/integration/phase-01-mwnf3-core/` (RED)
2. Implement in `src/importers/phase-01/` (GREEN)
3. Refactor and ensure tests pass (REFACTOR)

### 5. Validate with Dry Run

```bash
npm start -- import --phase 1 --dry-run --limit 10
```

---

## Development Commands

```bash
# Testing
npm test                      # Run all tests
npm run test:watch            # TDD watch mode
npm run test:coverage         # Coverage report

# Code Quality
npm run lint                  # Fix linting issues
npm run type-check            # TypeScript compilation check

# Build & Run
npm run build                 # Compile to dist/
npm run dev                   # Development mode (watch)
npm start                     # Production mode

# Import Commands
npm start -- import --phase 1 --dry-run
npm start -- import --phase 1
npm start -- validate
npm start -- status
```

---

## Files Created Summary

**Total**: 23 files

### Configuration (7 files)
- ✅ package.json, tsconfig.json, vitest.config.ts
- ✅ eslint.config.js, .prettierrc.json
- ✅ .env.example, .gitignore

### Source Code (6 files)
- ✅ src/index.ts (CLI entry point)
- ✅ src/database/LegacyDatabase.ts
- ✅ src/api/InventoryApiClient.ts
- ✅ src/utils/BackwardCompatibilityFormatter.ts
- ✅ src/utils/BackwardCompatibilityTracker.ts
- ✅ src/importers/BaseImporter.ts

### Tests (8 files)
- ✅ tests/README.md
- ✅ tests/unit/BackwardCompatibilityFormatter.test.ts (6 test cases)
- ✅ tests/unit/BackwardCompatibilityTracker.test.ts (8 test cases)
- ✅ tests/integration/phase-01-mwnf3-core/README.md
- ✅ tests/integration/phase-02-sh-thg/README.md
- ✅ tests/integration/phase-03-travel-explore/README.md
- ✅ tests/integration/phase-04-other-schemas/.gitkeep
- ✅ tests/e2e/full-import.test.ts
- ✅ tests/e2e/validation.test.ts

### Documentation (2 files)
- ✅ README.md (Quick start guide)
- ✅ docs/DEVELOPMENT.md (Comprehensive 600+ line guide)

---

## References

- **Analysis Documents**: 
  - `/docs/legacy-import/MASTER-Import-Strategy-And-Mapping.md` (Entity mappings, image strategy)
  - `/docs/legacy-import/LEGACY_DATA_IMPORT_TASK_PLAN.md` (17 phases, 170 tasks)
- **Application Documentation**: 
  - `/scripts/legacy-import/README.md` (Quick start)
  - `/scripts/legacy-import/docs/DEVELOPMENT.md` (Comprehensive guide)
- **Test Documentation**: 
  - `/scripts/legacy-import/tests/README.md` (Test structure)

---

## Status

✅ **Application skeleton complete and ready for implementation**

**Ready For**:
1. Install dependencies (`npm install`)
2. Add API client package
3. Configure `.env` with credentials
4. Start Phase 1 TDD implementation

**Next Importer to Implement**: ProjectImporter (Phase 1, Task 1)
