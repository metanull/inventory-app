# Script to add CreatesUsersWithPermissions trait and proper permissions to API tests
# This script updates all API test files to use proper permission-based test users

$testPath = "tests\Feature\Api"

# Define patterns for different test types
$readOnlyTests = @("IndexTest.php", "ShowTest.php", "AnonymousTest.php")
$createTests = @("StoreTest.php")
$updateTests = @("UpdateTest.php", "SetDefaultTest.php", "SetLaunchedTest.php", "SetEnabledTest.php")
$deleteTests = @("DestroyTest.php")

# Find all test files
$testFiles = Get-ChildItem -Path $testPath -Recurse -Filter "*Test.php"

$updatedCount = 0
$skippedCount = 0

foreach ($file in $testFiles) {
    $content = Get-Content $file.FullName -Raw
    
    # Skip if already has the trait
    if ($content -match "use CreatesUsersWithPermissions") {
        Write-Host "Skipping $($file.Name) - already has trait" -ForegroundColor Yellow
        $skippedCount++
        continue
    }
    
    # Skip if doesn't have the standard setUp pattern
    if ($content -notmatch "protected function setUp\(\): void") {
        Write-Host "Skipping $($file.Name) - non-standard setUp" -ForegroundColor Gray
        $skippedCount++
        continue
    }
    
    # Determine which permission method to use based on filename
    $permissionMethod = "createDataUser()" # Default to all permissions
    
    if ($readOnlyTests | Where-Object { $file.Name -like "*$_" }) {
        $permissionMethod = "createVisitorUser()"
    }
    
    # Add the trait import after other use statements
    $content = $content -replace "(use Tests\\TestCase;)", "`$1`nuse Tests\Traits\CreatesUsersWithPermissions;"
    
    # Add the trait to the class
    $content = $content -replace "(class \w+ extends TestCase\s*\{[\r\n\s]+use )([^;]+;)", "`${1}CreatesUsersWithPermissions, `$2"
    
    # Update the setUp method to create a user with permissions
    $oldSetUp = @"
    protected function setUp\(\): void
    \{
        parent::setUp\(\);
        \$this->user = User::factory\(\)->create\(\);
        \$this->actingAs\(\$this->user\);
    \}
"@
    
    $newSetUp = @"
    protected function setUp(): void
    {
        parent::setUp();
        // Create user with appropriate permissions
        `$this->user = `$this->$permissionMethod;
        `$this->actingAs(`$this->user);
    }
"@
    
    $content = $content -replace $oldSetUp, $newSetUp
    
    # Write back
    Set-Content -Path $file.FullName -Value $content -NoNewline
    
    Write-Host "Updated $($file.Name) with $permissionMethod" -ForegroundColor Green
    $updatedCount++
}

Write-Host "`nSummary:" -ForegroundColor Cyan
Write-Host "Updated: $updatedCount files" -ForegroundColor Green
Write-Host "Skipped: $skippedCount files" -ForegroundColor Yellow
