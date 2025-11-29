# SQL Importer v2 - Improvements and Fixes

## Summary of Changes

This document describes the cleanup and improvements made to the legacy import system, specifically focusing on the SQL-based importer (`sql-import-v2.ts`).

## Changes Made

### 1. Cleanup

#### Removed Abandoned Code

- **Deleted**: `src/sql-import.ts` - Abandoned importer with wrong architecture
- **Cleaned**: `package.json` scripts - Removed 25+ import-related scripts
  - Now only essential scripts remain: `build`, `test`, `lint`, `format`, `type-check`
  - Import scripts removed because they should be run directly via `npx tsx`

#### Rationale

- Reduces confusion by removing dead code
- Simplifies `package.json` - import commands are meant to be run ad-hoc, not as npm scripts
- Follows project principle: "Use `npx tsx` directly for data import operations"

### 2. Enhanced Logging

#### New `LogWriter` Utility

Created `src/sql-importers/utils/LogWriter.ts` to provide comprehensive logging:

**Features:**

- **Dual output**: Console (color-coded, user-friendly) + File (detailed, timestamped)
- **Structured logs**: Phase headers, importer start/complete, errors, summary
- **Automatic timestamps**: All log entries include ISO timestamps
- **Error tracking**: Captures first 10 errors per importer with full details
- **Progress tracking**: Duration tracking for each phase and importer

**Log File Location:**

```
scripts/legacy-import/logs/sql-import-YYYY-MM-DDTHH-MM-SS.log
```

**Example Log Output:**

```
================================================================================
SQL-BASED LEGACY IMPORT LOG
================================================================================
Start time: 2025-11-29T10:30:45.123Z
Log file: /path/to/logs/sql-import-2025-11-29T10-30-45-123Z.log

[2025-11-29T10:30:45.500Z] Connected to legacy database
[2025-11-29T10:30:45.750Z] Connected to new database

================================================================================
PHASE: PHASE 0: Reference Data
================================================================================
[2025-11-29T10:30:46.000Z] Starting LanguageSqlImporter...
[2025-11-29T10:30:47.250Z] Completed LanguageSqlImporter: 18 imported, 0 skipped, 0 errors (1.25s)
...
```

### 3. Import Order Alignment

Aligned `sql-import-v2.ts` with `index.ts` (API importer) to ensure identical import order:

#### Phase 0: Reference Data

1. **Languages** → `LanguageSqlImporter`
2. **Language Translations** → `LanguageTranslationSqlImporter` ⭐ NEW
3. **Countries** → `CountrySqlImporter`
4. **Country Translations** → `CountryTranslationSqlImporter` ⭐ NEW

#### Phase 1: Projects and Partners

5. **Projects** → `ProjectSqlImporter` (creates Contexts + Collections)
6. **Museums** → `MuseumSqlImporter` (Partner type: museum)
7. **Institutions** → `InstitutionSqlImporter` (Partner type: institution)

#### Phase 2: Items

8. **Objects** → `ObjectSqlImporter` (creates Authors, Artists, Tags)
9. **Monuments** → `MonumentSqlImporter` (creates Authors, Tags)

### 4. New SQL Importers

Created two new importers to match API importer functionality:

#### `LanguageTranslationSqlImporter`

- **Source**: `mwnf3.langnames` (legacy table)
- **Target**: `language_translations` (new table)
- **Purpose**: Import language names in different languages (e.g., "English" in French = "Anglais")
- **Logic**: Maps legacy 2-char codes to ISO 639-3, creates translation records

#### `CountryTranslationSqlImporter`

- **Source**: `mwnf3.countrynames` (legacy table)
- **Target**: `country_translations` (new table)
- **Purpose**: Import country names in different languages (e.g., "Spain" in Arabic)
- **Logic**: Maps legacy 2-char codes to ISO 3166-1 alpha-3, creates translation records

### 5. Schema Alignment

Verified all importers use correct:

- ✅ Table names match DDL definitions
- ✅ Column names match migration schemas
- ✅ Primary key constraints respected
- ✅ Foreign key dependencies resolved via `tracker` Map
- ✅ Backward compatibility strings formatted consistently

### 6. Error Handling

Improved error handling throughout:

- **Detailed error messages**: Include entity identifiers in all error logs
- **Error limit**: Console shows first 10 errors per importer, rest in log file
- **Stack traces**: Fatal errors include full stack trace in log
- **Graceful failure**: Each importer can fail independently without breaking the pipeline
- **Database cleanup**: Proper connection cleanup in `finally` block

## Architecture Principles

### DRY (Don't Repeat Yourself)

- **Helper classes**: `AuthorHelper`, `ArtistHelper`, `TagHelper` - reusable logic
- **Base class**: `BaseSqlImporter` - common functionality (logging, tracking, existence checks)
- **Shared utilities**: `CodeMappings`, `HtmlToMarkdownConverter`, `LogWriter`

### KISS (Keep It Simple, Stupid)

- **Single responsibility**: Each importer handles ONE entity type
- **Clear dependencies**: Phase-based execution ensures dependencies exist before use
- **Simple queries**: Direct SQL queries, no complex joins

### Single Responsibility

- **BaseSqlImporter**: Logging, tracking, utility methods
- **Concrete importers**: Entity-specific import logic ONLY
- **Helpers**: Reusable entity creation/lookup (authors, artists, tags)
- **LogWriter**: All logging concerns

### Isolation

- ✅ **No assumptions**: Importers don't assume existing data (except dependencies)
- ✅ **No seeder dependency**: Uses production JSON files or legacy DB directly
- ✅ **Idempotent**: Safe to run multiple times via backward_compatibility checks
- ✅ **Fresh database ready**: Designed to work on `db:wipe && migrate:refresh && MinimalDatabaseSeeder`

## Comparison: SQL vs API Importers

| Aspect           | SQL Importer                   | API Importer                   |
| ---------------- | ------------------------------ | ------------------------------ |
| **Speed**        | ⚡ 10-100x faster              | 🐌 Slow (individual API calls) |
| **Database**     | Direct SQL INSERT              | HTTP → API → Validation → ORM  |
| **Use Case**     | Production data import         | Testing, validation            |
| **Logging**      | Comprehensive (console + file) | Console only                   |
| **Dependencies** | Legacy DB, New DB              | Legacy DB, API server, Auth    |
| **Same Logic?**  | ✅ YES (aligned)               | ✅ YES (reference)             |

## Testing

### Type Safety

```powershell
cd E:\inventory\inventory-app\scripts\legacy-import
npm run type-check
```

Result: ✅ No TypeScript errors

### Linting

```powershell
npm run lint
```

### Running the Importer

```powershell
# Reset database first
cd E:\inventory\inventory-app
php artisan db:wipe
php artisan migrate:refresh
php artisan db:seed --class=MinimalDatabaseSeeder

# Run SQL importer
cd scripts\legacy-import
npx tsx src/sql-import-v2.ts
```

### Verifying Results

Use dump-data tool to compare legacy vs new data:

```powershell
npx tsx src/dump-data.ts all
```

## Future Improvements

### Potential Enhancements

1. **Parallel processing**: Import independent entities concurrently
2. **Incremental import**: Track last imported timestamp, import only new/updated records
3. **Rollback support**: Transaction-based import with rollback on failure
4. **Validation reports**: Compare legacy vs new data, report discrepancies
5. **Image import**: Currently not implemented

### Not Planned

- ❌ Converting back to API-based (SQL is faster, simpler)
- ❌ Merging with API importer (different use cases)

## Troubleshooting

### Common Issues

#### "Error: Unknown language code 'xx'"

**Cause**: Legacy database has language code not in `CodeMappings.ts`
**Fix**: Add mapping to `LANGUAGE_CODE_MAP` in `src/utils/CodeMappings.ts`

#### "Error: Unknown country code 'xx'"

**Cause**: Legacy database has country code not in `CodeMappings.ts`
**Fix**: Add mapping to `COUNTRY_CODE_MAP` in `src/utils/CodeMappings.ts`

#### "Cannot find backward_compatibility 'xxx'"

**Cause**: Dependency not imported (e.g., importing Objects before Projects)
**Fix**: Verify Phase 0 and Phase 1 completed successfully

#### "Duplicate entry for key 'backward_compatibility'"

**Cause**: Running importer multiple times without reset
**Solution**: This is SAFE - importer will skip existing records

### Debug Mode

To see detailed SQL queries, enable MySQL query logging:

```sql
-- In MySQL client
SET GLOBAL general_log = 'ON';
SET GLOBAL log_output = 'TABLE';
SELECT * FROM mysql.general_log ORDER BY event_time DESC LIMIT 100;
```

## Files Modified/Created

### Created

- `src/sql-importers/utils/LogWriter.ts` - Comprehensive logging utility
- `src/sql-importers/phase-00/LanguageTranslationSqlImporter.ts` - Language translations
- `src/sql-importers/phase-00/CountryTranslationSqlImporter.ts` - Country translations
- `docs/misc/SQL_IMPORTER_IMPROVEMENTS.md` - This document

### Modified

- `src/sql-import-v2.ts` - Enhanced logging, added missing importers, aligned order
- `package.json` - Removed 25+ unnecessary scripts
- `docs/misc/HOW_TO_RUN_IMPORT.md` - Updated with new instructions

### Deleted

- `src/sql-import.ts` - Abandoned importer with wrong architecture

## Conclusion

The SQL importer is now:

- ✅ **Aligned** with API importer (same order, same logic)
- ✅ **Complete** (includes language/country translations)
- ✅ **Well-logged** (comprehensive console + file output)
- ✅ **Type-safe** (passes TypeScript strict checks)
- ✅ **Maintainable** (DRY, KISS, Single Responsibility)
- ✅ **Reliable** (error handling, idempotent, isolated)

Ready for production use! 🚀
