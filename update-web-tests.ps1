# PowerShell script to update web tests for authorization requirements

$failingTests = @(
    "tests\Feature\Web\Projects\CreateTest.php",
    "tests\Feature\Web\Projects\EditTest.php", 
    "tests\Feature\Web\Projects\PaginationTest.php",
    "tests\Feature\Web\Projects\UpdateTest.php",
    "tests\Feature\Web\Projects\StoreTest.php",
    "tests\Feature\Web\Projects\DestroyTest.php",
    "tests\Feature\Web\Projects\IndexTest.php",
    "tests\Feature\Web\Contexts\CreateTest.php",
    "tests\Feature\Web\Contexts\EditTest.php",
    "tests\Feature\Web\Contexts\ShowTest.php",
    "tests\Feature\Web\Contexts\StoreTest.php",
    "tests\Feature\Web\Contexts\DestroyTest.php",
    "tests\Feature\Web\Contexts\PaginationTest.php",
    "tests\Feature\Web\Countries\EditTest.php",
    "tests\Feature\Web\Countries\StoreTest.php",
    "tests\Feature\Web\Countries\IndexTest.php",
    "tests\Feature\Web\Item\IndexTest.php",
    "tests\Feature\Web\Item\DestroyTest.php",
    "tests\Feature\Web\Item\PaginationTest.php",
    "tests\Feature\Web\Item\UpdateTest.php",
    "tests\Feature\Web\Languages\DestroyTest.php",
    "tests\Feature\Web\Languages\IndexTest.php",
    "tests\Feature\Web\Languages\StoreTest.php",
    "tests\Feature\Web\Partner\DestroyTest.php",
    "tests\Feature\Web\Partner\ShowTest.php",
    "tests\Feature\Web\Partner\UpdateTest.php",
    "tests\Feature\Web\Partner\IndexTest.php",
    "tests\Feature\Web\Collections\CreateTest.php",
    "tests\Feature\Web\Collections\DestroyTest.php",
    "tests\Feature\Web\Collections\EditTest.php",
    "tests\Feature\Web\Collections\IndexTest.php",
    "tests\Feature\Web\Collections\PaginationTest.php",
    "tests\Feature\Web\Collections\ShowTest.php",
    "tests\Feature\Web\Collections\UpdateTest.php",
    "tests\Feature\Web\Parity\LanguagesParityTest.php",
    "tests\Feature\Web\Parity\CountriesParityTest.php",
    "tests\Feature\Web\Parity\ProjectsParityTest.php"
)

foreach ($testFile in $failingTests) {
    $filePath = "e:\inventory\inventory-app\$testFile"
    
    if (Test-Path $filePath) {
        Write-Host "Updating $testFile..." -ForegroundColor Green
        
        $content = Get-Content $filePath -Raw
        
        # Add RequiresDataPermissions trait import
        if ($content -notmatch "use Tests\\Traits\\RequiresDataPermissions;") {
            $content = $content -replace "(use Tests\\TestCase;)", "`$1`nuse Tests\Traits\RequiresDataPermissions;"
        }
        
        # Add trait usage to class
        if ($content -notmatch "use RequiresDataPermissions;") {
            $content = $content -replace "(use RefreshDatabase;)", "`$1`n    use RequiresDataPermissions;"
        }
        
        # Update setUp method to use actAsRegularUser()
        if ($content -match "protected function setUp\(\): void\s*\{\s*parent::setUp\(\);\s*(\$this->user = User::factory\(\)->create\(\);\s*\$this->actingAs\(\$this->user\);|\$user = User::factory\(\)->create\(\);\s*\$this->actingAs\(\$user\);)") {
            $content = $content -replace "protected function setUp\(\): void\s*\{\s*parent::setUp\(\);\s*(\$this->user = User::factory\(\)->create\(\);\s*\$this->actingAs\(\$this->user\);|\$user = User::factory\(\)->create\(\);\s*\$this->actingAs\(\$user\);)", "protected function setUp(): void`n    {`n        parent::setUp();`n        `$this->actAsRegularUser();`n    }"
        }
        
        # Remove User import if it exists and isn't needed elsewhere
        if ($content -notmatch '\$user\s*=' -and $content -notmatch 'User::') {
            $content = $content -replace "use App\\Models\\User;\s*", ""
        }
        
        # Remove user property declaration if it exists
        $content = $content -replace "\s*protected \?\$?User \$user = null;\s*", "`n"
        
        Set-Content $filePath $content -NoNewline
    }
    else {
        Write-Host "File not found: $testFile" -ForegroundColor Red
    }
}

Write-Host "Updated all failing web tests!" -ForegroundColor Yellow