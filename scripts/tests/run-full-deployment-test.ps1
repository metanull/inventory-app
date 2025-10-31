<#
.SYNOPSIS
  Full deployment test for InventoryApp deployment module.

.DESCRIPTION
  Creates a complete temporary deployment environment and runs the full Deploy-Application
  workflow (including PHP artisan mocking). Tests all 8 deployment phases in a safe,
  temporary sandbox. No admin privileges required - operates entirely in %TEMP%.

  Use -NoCleanup to inspect the test environment after execution.

.PARAMETER NoCleanup
  When specified, do not remove the temporary test directories.

.EXAMPLE
  .\scripts\tests\run-full-deployment-test.ps1
  .\scripts\tests\run-full-deployment-test.ps1 -NoCleanup
#>

[CmdletBinding()]
param(
    [switch] $NoCleanup
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

Write-Host "`n╔════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║   FULL DEPLOYMENT TEST - InventoryApp Module              ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan

# ============================================================================
# SETUP
# ============================================================================

$timestamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$baseTemp = Join-Path $env:TEMP "inventory-app-full-deploy-test-$timestamp"
$packagePath = Join-Path $baseTemp 'package'
$webserverParent = Join-Path $baseTemp 'wwwroot'
$webserverPath = Join-Path $webserverParent 'inventory-app'
$sharedRoot = Join-Path $baseTemp 'github-apps'

Write-Host "`nTest Environment:" -ForegroundColor Yellow
Write-Host "  Base:          $baseTemp" -ForegroundColor Gray
Write-Host "  Package:       $packagePath" -ForegroundColor Gray
Write-Host "  Webserver:     $webserverPath" -ForegroundColor Gray
Write-Host "  Shared Root:   $sharedRoot" -ForegroundColor Gray

# Create directories
Write-Host "`n[SETUP] Creating environment structure..." -ForegroundColor Cyan
New-Item -Path $packagePath -ItemType Directory -Force | Out-Null
New-Item -Path $webserverParent -ItemType Directory -Force | Out-Null
New-Item -Path $sharedRoot -ItemType Directory -Force | Out-Null

# Create Laravel package structure
$laravelDirs = @('app', 'routes', 'resources', 'public', 'database', 'bootstrap', 'config', 'storage')
foreach ($dir in $laravelDirs) {
    New-Item -Path (Join-Path $packagePath $dir) -ItemType Directory -Force | Out-Null
}

# Create required files
Set-Content -Path (Join-Path $packagePath '.env.example') `
    -Value @"
APP_NAME=Inventory App
APP_ENV=production
APP_KEY=base64:test
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_db
DB_USERNAME=app
DB_PASSWORD=password
DB_SSLMODE=prefer
"@ -Encoding UTF8

Set-Content -Path (Join-Path $packagePath 'artisan') -Value "#!/usr/bin/env php`n" -Encoding UTF8
Set-Content -Path (Join-Path $packagePath 'composer.lock') -Value "{}" -Encoding UTF8

Write-Host "  ✓ Environment created" -ForegroundColor Green

# ============================================================================
# IMPORT MODULE
# ============================================================================

Write-Host "`n[IMPORT] Loading deployment module..." -ForegroundColor Cyan
$moduleDir = Join-Path (Split-Path -Parent $MyInvocation.MyCommand.Path) '..\InventoryApp.Deployment'
$moduleFull = (Resolve-Path $moduleDir -ErrorAction Stop).ProviderPath

try {
    Import-Module $moduleFull -Force -ErrorAction Stop
    Write-Host "  ✓ Module imported successfully" -ForegroundColor Green
} catch {
    Write-Host "  ✗ Failed to import module: $_" -ForegroundColor Red
    if (-not $NoCleanup) { Remove-Item -Path $baseTemp -Recurse -Force -ErrorAction SilentlyContinue }
    exit 1
}

# ============================================================================
# FULL DEPLOYMENT TEST
# ============================================================================

Write-Host "`n[DEPLOYMENT] Starting full deployment workflow..." -ForegroundColor Cyan

try {
# Prepare credentials
    $dbPassword = ConvertTo-SecureString "testpassword" -AsPlainText -Force
    $dbCredential = New-Object System.Management.Automation.PSCredential("app", $dbPassword)

    # Get PHP path from composer or use system php
    $phpPath = if (Test-Path "e:\inventory\inventory-app\php.exe") {
        "e:\inventory\inventory-app\php.exe"
    } else {
        # Try to resolve php from PATH
        $phpCmd = Get-Command php -ErrorAction SilentlyContinue
        if ($phpCmd) {
            $phpCmd.Source
        } else {
            throw "PHP not found in system PATH"
        }
    }

    Write-Host "Using PHP: $phpPath" -ForegroundColor Gray

    # Run deployment
    $deploymentParams = @{
        DeploymentPackagePath = $packagePath
        WebserverPath         = $webserverPath
        SharedStorageRoot     = $sharedRoot
        PhpPath               = $phpPath
        AppUrl                = "http://localhost:8000"
        AppName               = "Test Inventory App"
        AppEnv                = "staging"
        AppKey                = "base64:1234567890abcdef=="
        DatabaseCredential    = $dbCredential
        DatabaseHost          = "127.0.0.1"
        DatabasePort          = 3306
        DatabaseName          = "test_inventory"
        DatabaseSslMode       = "prefer"
        KeepStagingCount      = 3
    }

    $success = Deploy-Application @deploymentParams

    if (-not $success) {
        Write-Host "`n✗ Deployment failed" -ForegroundColor Red
        if (-not $NoCleanup) { Remove-Item -Path $baseTemp -Recurse -Force -ErrorAction SilentlyContinue }
        exit 1
    }

} catch {
    Write-Host "`n✗ Deployment error: $_" -ForegroundColor Red
    Write-Host "Stack trace: $($_.ScriptStackTrace)" -ForegroundColor DarkRed
    if (-not $NoCleanup) { Remove-Item -Path $baseTemp -Recurse -Force -ErrorAction SilentlyContinue }
    exit 1
}

# ============================================================================
# VERIFICATION
# ============================================================================

Write-Host "`n[VERIFY] Validating deployment artifacts..." -ForegroundColor Cyan

$verificationsOk = $true

# 1. Check production symlink exists
if (Test-Path $webserverPath) {
    Write-Host "  ✓ Production symlink created" -ForegroundColor Green
} else {
    Write-Host "  ✗ Production symlink missing" -ForegroundColor Red
    $verificationsOk = $false
}

# 2. Check storage symlink exists
$storageLink = Join-Path $webserverPath 'storage'
if (Test-Path $storageLink) {
    Write-Host "  ✓ Storage symlink created" -ForegroundColor Green
} else {
    Write-Host "  ✗ Storage symlink missing" -ForegroundColor Red
    $verificationsOk = $false
}

# 3. Check .env file created
$envFile = Join-Path $webserverPath '.env'
if (Test-Path $envFile) {
    Write-Host "  ✓ .env file created" -ForegroundColor Green
} else {
    Write-Host "  ✗ .env file missing" -ForegroundColor Red
    $verificationsOk = $false
}

# 4. Check shared storage structure
$sharedStoragePath = Join-Path $sharedRoot 'shared-storage' 'storage'
$storageSubdirs = @('app', 'logs', 'framework/cache', 'framework/sessions', 'framework/views')
$allSubdirsExist = $true
foreach ($subdir in $storageSubdirs) {
    $subPath = Join-Path $sharedStoragePath $subdir
    if (-not (Test-Path $subPath)) {
        $allSubdirsExist = $false
        Write-Host "  ✗ Missing shared storage subdirectory: $subdir" -ForegroundColor Red
        $verificationsOk = $false
    }
}
if ($allSubdirsExist) {
    Write-Host "  ✓ Shared storage structure complete" -ForegroundColor Green
}

# 5. Check staging directory exists
$stagingParent = Split-Path $webserverPath -Parent
$stagingDirs = @(Get-ChildItem -Path $stagingParent -Directory -Filter 'staging-*' -ErrorAction SilentlyContinue)
if ($stagingDirs.Length -gt 0) {
    Write-Host "  ✓ Staging directory created ($($stagingDirs.Length) available)" -ForegroundColor Green
} else {
    Write-Host "  ✗ No staging directories found" -ForegroundColor Red
    $verificationsOk = $false
}

# ============================================================================
# SUMMARY
# ============================================================================

Write-Host "`n╔════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
if ($verificationsOk) {
    Write-Host "║   ✓ FULL DEPLOYMENT TEST PASSED                           ║" -ForegroundColor Green
} else {
    Write-Host "║   ✗ DEPLOYMENT TEST FAILED - VERIFICATION ERRORS          ║" -ForegroundColor Red
}
Write-Host "╚════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan

Write-Host "`nTest Data Location: $baseTemp" -ForegroundColor Yellow

if (-not $NoCleanup) {
    Write-Host "Cleaning up..." -ForegroundColor Cyan
    Remove-Item -Path $baseTemp -Recurse -Force -ErrorAction SilentlyContinue
    Write-Host "  ✓ Cleanup complete" -ForegroundColor Green
} else {
    Write-Host "Keeping test data for inspection (-NoCleanup flag used)" -ForegroundColor Yellow
}

Write-Host ""

if ($verificationsOk) { exit 0 } else { exit 1 }
