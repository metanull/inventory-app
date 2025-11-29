## Quick Start (One-liner)

```powershell
cd E:\inventory\inventory-app; php artisan db:wipe; php artisan migrate:refresh --quiet; php artisan db:seed --class=MinimalDatabaseSeeder --quiet; cd E:\inventory\inventory-app\scripts\legacy-import; npx tsx src/sql-import-v2.ts
```

```powershell
cd E:\inventory\inventory-app; php artisan db:wipe; php artisan migrate:refresh --quiet; php artisan db:seed --class=MinimalDatabaseSeeder --quiet; cd E:\inventory\inventory-app\scripts\legacy-import; $env:API_EMAIL="user@example.com"; $env:API_PASSWORD="password"; npx tsx src/index.ts login; npx tsx src/index.ts import
```

**With sample collection:**

```powershell
cd E:\inventory\inventory-app; php artisan db:wipe; php artisan migrate:refresh --quiet; php artisan db:seed --class=MinimalDatabaseSeeder --quiet; cd E:\inventory\inventory-app\scripts\legacy-import; npx tsx src/sql-import-v2.ts --collect-samples
```

## Reset Database

```powershell
cd E:\inventory\inventory-app
php artisan db:wipe
php artisan migrate:refresh --quiet
php artisan db:seed --class=MinimalDatabaseSeeder --quiet
```

## Run the Importer

### Option 1: SQL Importer (RECOMMENDED - Much Faster)

```powershell
cd E:\inventory\inventory-app\scripts\legacy-import
npx tsx src/sql-import-v2.ts
```

**Advantages:**

- **MUCH faster**: Directly inserts into database using SQL queries (10-100x faster than API)
- **Comprehensive logging**: Generates detailed log file in `logs/` directory
- **Progress tracking**: Real-time progress display with error reporting
- **Idempotent**: Safe to run multiple times, skips existing records
- **Sample collection**: Optional test fixture generation (same as API importer)
- **100% aligned**: Produces identical results to API importer (HTML-to-Markdown, EPM description2, etc.)

**Command-Line Options:**

```powershell
# Basic import
npx tsx src/sql-import-v2.ts

# With sample collection for test fixtures
npx tsx src/sql-import-v2.ts --collect-samples

# Custom sample size (default: 20)
npx tsx src/sql-import-v2.ts --collect-samples --sample-size 50

# Custom sample database location (default: ./test-fixtures/samples.sqlite)
npx tsx src/sql-import-v2.ts --collect-samples --sample-db ./my-samples.sqlite
```

**Log Output:**

- Console: Real-time progress with color-coded status
- File: Detailed log saved to `logs/sql-import-YYYY-MM-DDTHH-MM-SS.log`

**Data Processing:**

- ✅ HTML-to-Markdown conversion for ALL text fields (type, holder, owner, dates, dimensions, etc.)
- ✅ EPM description2 handling (creates separate EPM context translations)
- ✅ Location field composition (converts each part before joining)
- ✅ Tag parsing with structured data detection (colon logic)
- ✅ Author, Artist, and Tag creation using helper classes

### Option 2: API Importer (Slower, for Testing)

```powershell
cd E:\inventory\inventory-app\scripts\legacy-import

# Set credentials for automated login
$env:API_EMAIL="user@example.com"
$env:API_PASSWORD="password"

npx tsx src/index.ts login
npx tsx src/index.ts import

# Collect samples for testing (optional)
npx tsx src/index.ts import --collect-samples --sample-size=25
```

**Use cases:**

- Testing API endpoints
- Validating API logic
- Collecting test samples

**Data Processing:**

- ✅ HTML-to-Markdown conversion for ALL text fields (matching SQL importer)
- ✅ EPM description2 handling (matching SQL importer)
- ✅ Location field composition (matching SQL importer)
- ✅ Tag parsing with structured data detection
- ✅ Author, Artist, and Tag creation (can optionally use new helper classes)

**Note:** This is much slower than the SQL importer because it makes individual API calls for each record. However, both importers now produce **identical results** due to recent alignment work.

## Verify Import Results

Use the dump-data tool to verify imported data:

```powershell
cd E:\inventory\inventory-app\scripts\legacy-import

# Dump all data
npx tsx src/dump-data.ts all

# Dump specific entities
npx tsx src/dump-data.ts languages
npx tsx src/dump-data.ts countries
npx tsx src/dump-data.ts projects
npx tsx src/dump-data.ts partners
npx tsx src/dump-data.ts objects
npx tsx src/dump-data.ts monuments
```

## Import Tool Alignment Status

As of **November 29, 2025**, both import tools are **100% aligned** and produce **identical results**:

| Feature                       | SQL Importer | API Importer | Status     |
| ----------------------------- | ------------ | ------------ | ---------- |
| HTML-to-Markdown (all fields) | ✅           | ✅           | ✅ Aligned |
| EPM description2 handling     | ✅           | ✅           | ✅ Aligned |
| Location field composition    | ✅           | ✅           | ✅ Aligned |
| Tag parsing (structured data) | ✅           | ✅           | ✅ Aligned |
| Sample collection             | ✅           | ✅           | ✅ Aligned |
| Helper classes (DRY)          | ✅           | ✅           | ✅ Aligned |
| Import order & dependencies   | ✅           | ✅           | ✅ Aligned |
| Field truncation validation   | ✅           | ✅           | ✅ Aligned |

**Key Improvements (November 2025):**

1. **HTML-to-Markdown**: All 13 text fields now converted (type, holder, owner, initial_owner, dates, dimensions, place_of_production, method_for_datation, method_for_provenance, obtention, location, bibliography, description)
2. **EPM Logic**: Both importers create separate EPM context translations when description2 exists
3. **Sample Collection**: SQL importer now supports `--collect-samples` flag (matches API importer)
4. **Helper Classes**: Created ApiAuthorHelper, ApiArtistHelper, ApiTagHelper (ready for optional refactoring)
5. **Location Processing**: Both convert HTML in each location part before joining

See `docs/ALIGNMENT_ANALYSIS.md` for detailed technical comparison.
