```powershell
cd E:\inventory\inventory-app\scripts\legacy-import
pushd E:\inventory\inventory-app
php artisan db:wipe
php artisan migrate:refresh
php artisan db:seed --class=MinimalDatabaseSeeder
popd

# Set credentials for automated login
$env:API_EMAIL="user@example.com"
$env:API_PASSWORD="password"

npx tsx src/index.ts login
# npx tsx src/index.ts import --collect-samples --sample-size=25        # Collect smaples for the tests
npx tsx src/index.ts import
```
