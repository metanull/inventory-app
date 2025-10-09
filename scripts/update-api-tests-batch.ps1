# PowerShell script to update API test files with proper permissions
# This script updates test files to use the CreatesUsersWithPermissions trait

$testDir = "tests/Feature/Api"
$filesUpdated = 0
$filesSkipped = 0
$errors = @()

# Define the patterns and replacements for each test type
$patterns = @{
    'IndexTest' = @{
        'OldUse' = 'use RefreshDatabase, WithFaker;'
        'NewUse' = 'use CreatesUsersWithPermissions, RefreshDatabase, WithFaker;'
        'OldImport' = 'use Tests\TestCase;'
        'NewImport' = "use Tests\TestCase;`nuse Tests\Traits\CreatesUsersWithPermissions;"
        'OldSetup' = "`$this->user = User::factory()->create();`n        `$this->actingAs(`$this->user);"
        'NewSetup' = "// Create user with VIEW_DATA permission for read operations`n        `$this->user = `$this->createVisitorUser();`n        `$this->actingAs(`$this->user);"
    }
    'ShowTest' = @{
        'OldUse' = 'use RefreshDatabase, WithFaker;'
        'NewUse' = 'use CreatesUsersWithPermissions, RefreshDatabase, WithFaker;'
        'OldImport' = 'use Tests\TestCase;'
        'NewImport' = "use Tests\TestCase;`nuse Tests\Traits\CreatesUsersWithPermissions;"
        'OldSetup' = "`$this->user = User::factory()->create();`n        `$this->actingAs(`$this->user);"
        'NewSetup' = "// Create user with VIEW_DATA permission for read operations`n        `$this->user = `$this->createVisitorUser();`n        `$this->actingAs(`$this->user);"
    }
    'StoreTest' = @{
        'OldUse' = 'use RefreshDatabase, WithFaker;'
        'NewUse' = 'use CreatesUsersWithPermissions, RefreshDatabase, WithFaker;'
        'OldImport' = 'use Tests\TestCase;'
        'NewImport' = "use Tests\TestCase;`nuse Tests\Traits\CreatesUsersWithPermissions;"
        'OldSetup' = "`$this->user = User::factory()->create();`n        `$this->actingAs(`$this->user);"
        'NewSetup' = "// Create user with CREATE_DATA permission for create operations`n        `$this->user = `$this->createUserWithPermissions(['create data']);`n        `$this->actingAs(`$this->user);"
    }
    'UpdateTest' = @{
        'OldUse' = 'use RefreshDatabase, WithFaker;'
        'NewUse' = 'use CreatesUsersWithPermissions, RefreshDatabase, WithFaker;'
        'OldImport' = 'use Tests\TestCase;'
        'NewImport' = "use Tests\TestCase;`nuse Tests\Traits\CreatesUsersWithPermissions;"
        'OldSetup' = "`$this->user = User::factory()->create();`n        `$this->actingAs(`$this->user);"
        'NewSetup' = "// Create user with UPDATE_DATA permission for update operations`n        `$this->user = `$this->createUserWithPermissions(['update data']);`n        `$this->actingAs(`$this->user);"
    }
    'DestroyTest' = @{
        'OldUse' = 'use RefreshDatabase, WithFaker;'
        'NewUse' = 'use CreatesUsersWithPermissions, RefreshDatabase, WithFaker;'
        'OldImport' = 'use Tests\TestCase;'
        'NewImport' = "use Tests\TestCase;`nuse Tests\Traits\CreatesUsersWithPermissions;"
        'OldSetup' = "`$this->user = User::factory()->create();`n        `$this->actingAs(`$this->user);"
        'NewSetup' = "// Create user with DELETE_DATA permission for delete operations`n        `$this->user = `$this->createUserWithPermissions(['delete data']);`n        `$this->actingAs(`$this->user);"
    }
}

# Get all test files that need updating
$testFiles = Get-ChildItem -Path $testDir -Recurse -Filter "*Test.php" | Where-Object {
    $_.Name -match '(Index|Show|Store|Update|Destroy)Test\.php$' -and
    $_.FullName -notmatch 'Anonymous' # Skip AnonymousTest files
}

Write-Host "Found $($testFiles.Count) test files to process" -ForegroundColor Cyan
Write-Host ""

foreach ($file in $testFiles) {
    try {
        $content = Get-Content $file.FullName -Raw
        
        # Skip if already using CreatesUsersWithPermissions
        if ($content -match 'use Tests\\Traits\\CreatesUsersWithPermissions;') {
            Write-Host "✓ SKIP: $($file.Name) - Already updated" -ForegroundColor Yellow
            $filesSkipped++
            continue
        }
        
        # Skip if doesn't have the standard setup pattern
        if ($content -notmatch '\$this->user = User::factory\(\)->create\(\);') {
            Write-Host "⚠ SKIP: $($file.Name) - Non-standard setup pattern" -ForegroundColor Gray
            $filesSkipped++
            continue
        }
        
        # Determine test type
        $testType = $null
        foreach ($type in $patterns.Keys) {
            if ($file.Name -match $type) {
                $testType = $type
                break
            }
        }
        
        if (-not $testType) {
            Write-Host "⚠ SKIP: $($file.Name) - Unknown test type" -ForegroundColor Gray
            $filesSkipped++
            continue
        }
        
        $pattern = $patterns[$testType]
        $modified = $false
        
        # Add import if not present
        if ($content -match 'use Tests\\TestCase;' -and $content -notmatch 'use Tests\\Traits\\CreatesUsersWithPermissions;') {
            $content = $content -replace '(use Tests\\TestCase;)', $pattern.NewImport
            $modified = $true
        }
        
        # Update use statement
        if ($content -match 'use RefreshDatabase, WithFaker;') {
            $content = $content -replace 'use RefreshDatabase, WithFaker;', $pattern.NewUse
            $modified = $true
        }
        
        # Update setUp method
        if ($content -match [regex]::Escape($pattern.OldSetup)) {
            $content = $content -replace [regex]::Escape($pattern.OldSetup), $pattern.NewSetup
            $modified = $true
        }
        
        if ($modified) {
            Set-Content -Path $file.FullName -Value $content -NoNewline
            Write-Host "✓ UPDATED: $($file.Name)" -ForegroundColor Green
            $filesUpdated++
        } else {
            Write-Host "⚠ SKIP: $($file.Name) - No changes needed" -ForegroundColor Gray
            $filesSkipped++
        }
        
    } catch {
        $errorMsg = "✗ ERROR: $($file.Name) - $($_.Exception.Message)"
        Write-Host $errorMsg -ForegroundColor Red
        $errors += $errorMsg
    }
}

Write-Host ""
Write-Host "================================" -ForegroundColor Cyan
Write-Host "Summary:" -ForegroundColor Cyan
Write-Host "  Updated: $filesUpdated" -ForegroundColor Green
Write-Host "  Skipped: $filesSkipped" -ForegroundColor Yellow
Write-Host "  Errors:  $($errors.Count)" -ForegroundColor $(if ($errors.Count -gt 0) { 'Red' } else { 'Green' })
Write-Host "================================" -ForegroundColor Cyan

if ($errors.Count -gt 0) {
    Write-Host ""
    Write-Host "Errors encountered:" -ForegroundColor Red
    $errors | ForEach-Object { Write-Host "  $_" -ForegroundColor Red }
}
