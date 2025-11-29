## Oneliner

cd E:\inventory\inventory-app; `
php artisan db:wipe; `
php artisan migrate:refresh --quiet; `
php artisan db:seed --class=MinimalDatabaseSeeder --quiet; `
cd E:\inventory\inventory-app\scripts\legacy-import; `
npx tsx src/sql-import-v2.ts

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

**Log Output:**
- Console: Real-time progress with color-coded status
- File: Detailed log saved to `logs/sql-import-YYYY-MM-DDTHH-MM-SS.log`

### Option 2: API Importer (Slower, for Testing)
```powershell
cd E:\inventory\inventory-app\scripts\legacy-import

# Set credentials for automated login
$env:API_EMAIL="user@example.com"
$env:API_PASSWORD="password"

npx tsx src/index.ts login
npx tsx src/index.ts import

# Collect samples for testing (optional)
# npx tsx src/index.ts import --collect-samples --sample-size=25
```

**Use cases:**
- Testing API endpoints
- Validating API logic
- Collecting test samples

**Note:** This is much slower than the SQL importer because it makes individual API calls for each record.

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