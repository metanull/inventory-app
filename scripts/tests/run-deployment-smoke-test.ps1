<#
.SYNOPSIS
  Smoke test for InventoryApp deployment module.

.DESCRIPTION
  Creates a temporary deployment package and staging area under $env:TEMP, imports
  the `InventoryApp.Deployment` module and runs a subset of functions that do not
  require PHP or an actual webserver. Verifies expected artifacts are produced.

  Run as Administrator. This script operates only in temporary directories and
  cleans up after itself by default. Use -NoCleanup to inspect results.

.PARAMETER NoCleanup
  When specified, do not remove the temporary test directories so you can inspect results.

.EXAMPLE
  # Run the smoke test (recommended as Administrator)
  .\scripts\tests\run-deployment-smoke-test.ps1
#>

[CmdletBinding()]
param(
    [switch] $NoCleanup
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

Write-Host "Starting InventoryApp deployment smoke test" -ForegroundColor Cyan

# Prepare temp paths
$timestamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$baseTemp = Join-Path $env:TEMP "inventory-app-deploy-test-$timestamp"
$packagePath = Join-Path $baseTemp 'package'
$sharedRoot = Join-Path $baseTemp 'github-apps'

Write-Host "Using temp base: $baseTemp" -ForegroundColor Yellow

# Create minimal package structure
Write-Host "Creating minimal package structure..." -ForegroundColor Yellow
New-Item -Path $packagePath -ItemType Directory -Force | Out-Null
New-Item -Path (Join-Path $packagePath 'app') -ItemType Directory -Force | Out-Null
New-Item -Path (Join-Path $packagePath 'routes') -ItemType Directory -Force | Out-Null
New-Item -Path (Join-Path $packagePath 'resources') -ItemType Directory -Force | Out-Null
New-Item -Path (Join-Path $packagePath 'public') -ItemType Directory -Force | Out-Null
New-Item -Path (Join-Path $packagePath 'database') -ItemType Directory -Force | Out-Null
New-Item -Path (Join-Path $packagePath 'bootstrap') -ItemType Directory -Force | Out-Null
New-Item -Path (Join-Path $packagePath 'config') -ItemType Directory -Force | Out-Null

# Create required files
Set-Content -Path (Join-Path $packagePath '.env.example') -Value "APP_NAME=Inventory App`nAPP_ENV=local" -Encoding UTF8
Set-Content -Path (Join-Path $packagePath 'artisan') -Value "#!/usr/bin/env php`n" -Encoding UTF8
Set-Content -Path (Join-Path $packagePath 'composer.lock') -Value "{}" -Encoding UTF8

# Ensure module path exists
$moduleDir = Join-Path (Split-Path -Parent $MyInvocation.MyCommand.Path) '..\InventoryApp.Deployment' | Resolve-Path -ErrorAction SilentlyContinue
if (-not $moduleDir) {
    # fallback known repo path
    $moduleDir = Join-Path $PSScriptRoot '..\InventoryApp.Deployment'
}
$moduleFull = (Resolve-Path $moduleDir -ErrorAction SilentlyContinue)
if (-not $moduleFull) {
    Write-Error "Deployment module not found at expected location. Ensure you run this script from the project root."
    exit 1
}
$moduleFull = $moduleFull.ProviderPath

Write-Host "Importing deployment module from: $moduleFull" -ForegroundColor Yellow
Import-Module $moduleFull -Force -ErrorAction Stop

# Test 1: Validate deployment package
Write-Host "\nTEST 1: Test-DeploymentPackage" -ForegroundColor Cyan
try {
    Test-DeploymentPackage -PackagePath $packagePath -ErrorAction Stop
    Write-Host "  ✓ Test-DeploymentPackage passed" -ForegroundColor Green
} catch {
    Write-Host "  ✗ Test-DeploymentPackage failed: $_" -ForegroundColor Red
    if (-not $NoCleanup) { Remove-Item -Path $baseTemp -Recurse -Force -ErrorAction SilentlyContinue }
    exit 1
}

# Test 2: Create staging directory
Write-Host "\nTEST 2: New-StagingDirectory" -ForegroundColor Cyan
try {
    $stagingPath = New-StagingDirectory -BaseDirectory $sharedRoot
    Write-Host "  ✓ Staging created at: $stagingPath" -ForegroundColor Green
} catch {
    Write-Host "  ✗ New-StagingDirectory failed: $_" -ForegroundColor Red
    if (-not $NoCleanup) { Remove-Item -Path $baseTemp -Recurse -Force -ErrorAction SilentlyContinue }
    exit 1
}

# Copy package contents into staging (simulate deployment copy step)
Write-Host "Copying package into staging..." -ForegroundColor Yellow
Copy-Item -Path (Join-Path $packagePath '*') -Destination $stagingPath -Recurse -Force -ErrorAction Stop

# Test 3: Configure storage symlink
Write-Host "\nTEST 3: New-StorageSymlink" -ForegroundColor Cyan
try {
    New-StorageSymlink -StagingPath $stagingPath -SharedStorageRoot $sharedRoot
    $stagingStorage = Join-Path $stagingPath 'storage'
    if (Test-Path $stagingStorage) {
        Write-Host "  ✓ Storage symlink/junction created at: $stagingStorage" -ForegroundColor Green
    } else {
        throw "Storage path missing after symlink creation"
    }
} catch {
    Write-Host "  ✗ New-StorageSymlink failed: $_" -ForegroundColor Red
    if (-not $NoCleanup) { Remove-Item -Path $baseTemp -Recurse -Force -ErrorAction SilentlyContinue }
    exit 1
}

# Test 4: Remove-OldStagingDirectories (create a few extra dirs to test cleanup)
Write-Host "\nTEST 4: Remove-OldStagingDirectories" -ForegroundColor Cyan
try {
    # Create extra staging dirs
    1..5 | ForEach-Object { New-StagingDirectory -BaseDirectory $sharedRoot | Out-Null }
    # Run cleanup keeping 3
    Remove-OldStagingDirectories -BaseDirectory $sharedRoot -KeepCount 3
    Write-Host "  ✓ Remove-OldStagingDirectories executed (old directories pruned)" -ForegroundColor Green
} catch {
    Write-Host "  ✗ Remove-OldStagingDirectories failed: $_" -ForegroundColor Red
    if (-not $NoCleanup) { Remove-Item -Path $baseTemp -Recurse -Force -ErrorAction SilentlyContinue }
    exit 1
}

# Final verification
Write-Host "\nSmoke test completed successfully." -ForegroundColor Green
Write-Host "Temporary test data is located at: $baseTemp" -ForegroundColor Yellow
if (-not $NoCleanup) {
    Write-Host "Cleaning up temporary files..." -ForegroundColor Yellow
    Remove-Item -Path $baseTemp -Recurse -Force -ErrorAction SilentlyContinue
    Write-Host "Cleanup complete." -ForegroundColor Yellow
} else {
    Write-Host "No cleanup performed (-NoCleanup passed). Inspect $baseTemp manually." -ForegroundColor Yellow
}

Write-Host "\nNotes:" -ForegroundColor Cyan
Write-Host " - This smoke test intentionally does NOT invoke PHP-dependent functions (migrations, artisan) to keep it safe. To perform a full deployment test, run the Deploy-Application script as Administrator on a test host with PHP 8.2+ installed." -ForegroundColor White

Write-Host "\n✓ All tests passed!" -ForegroundColor Green
exit 0
