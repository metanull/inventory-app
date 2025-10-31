<#
.SYNOPSIS
  End-to-end deployment test with real GitHub release.

.DESCRIPTION
  Downloads the latest release from GitHub, extracts it, and runs the full deployment
  workflow with real composer, artisan, and npm commands. Tests the complete deployment
  pipeline in a temporary sandbox environment.

  No admin privileges required - operates entirely in %TEMP%.
  Use -NoCleanup to inspect the test environment after execution.

.PARAMETER ReleaseUrl
  URL to the release ZIP file. Defaults to latest v6.0.1 tag.

.PARAMETER ExpandArchive
  When specified, extracts the ZIP before passing to Deploy-Application (tests archive expansion in test).
  When not specified (default), passes the ZIP file directly to the module for it to extract.

.PARAMETER NoCleanup
  When specified, do not remove the temporary test directories.

.PARAMETER Verbosity
  Output verbosity level. Options: Silent, Error, Warning, Information (default), Verbose, Debug

.EXAMPLE
  .\scripts\tests\run-e2e-deployment-test.ps1
  .\scripts\tests\run-e2e-deployment-test.ps1 -NoCleanup
  .\scripts\tests\run-e2e-deployment-test.ps1 -ExpandArchive
  .\scripts\tests\run-e2e-deployment-test.ps1 -Verbosity Verbose
#>

[CmdletBinding()]
param(
    [string] $ReleaseUrl = "https://github.com/metanull/inventory-app/archive/refs/tags/v6.0.1.zip",
    [switch] $ExpandArchive,
    [switch] $NoCleanup,
    [ValidateSet('Silent', 'Error', 'Warning', 'Information', 'Verbose', 'Debug')]
    [string] $Verbosity = 'Information'
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'
$ProgressPreference = 'SilentlyContinue'

# Set verbosity preference
switch ($Verbosity) {
    'Silent' { $InformationPreference = 'SilentlyContinue'; $VerbosePreference = 'SilentlyContinue'; $DebugPreference = 'SilentlyContinue' }
    'Error' { $InformationPreference = 'SilentlyContinue'; $VerbosePreference = 'SilentlyContinue'; $DebugPreference = 'SilentlyContinue' }
    'Warning' { $InformationPreference = 'SilentlyContinue'; $VerbosePreference = 'SilentlyContinue'; $DebugPreference = 'SilentlyContinue' }
    'Information' { $InformationPreference = 'Continue'; $VerbosePreference = 'SilentlyContinue'; $DebugPreference = 'SilentlyContinue' }
    'Verbose' { $InformationPreference = 'Continue'; $VerbosePreference = 'Continue'; $DebugPreference = 'SilentlyContinue' }
    'Debug' { $InformationPreference = 'Continue'; $VerbosePreference = 'Continue'; $DebugPreference = 'Continue' }
}

Write-Host "`n╔════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║   E2E DEPLOYMENT TEST - Real Release from GitHub           ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan

# ============================================================================
# SETUP
# ============================================================================

$timestamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$baseTemp = Join-Path $env:TEMP "inventory-app-e2e-deploy-test-$timestamp"
$downloadPath = Join-Path $baseTemp 'release.zip'
$extractPath = Join-Path $baseTemp 'extract'
$packagePath = if ($ExpandArchive) { Join-Path $extractPath 'inventory-app-6.0.1' } else { $downloadPath }
$webserverParent = Join-Path $baseTemp 'wwwroot'
$webserverPath = Join-Path $webserverParent 'inventory-app'
$sharedRoot = Join-Path $baseTemp 'github-apps'

Write-Host "`nTest Environment:" -ForegroundColor Yellow
Write-Host "  Base:          $baseTemp" -ForegroundColor Gray
Write-Host "  Release URL:   $ReleaseUrl" -ForegroundColor Gray
Write-Host "  ExpandArchive: $ExpandArchive" -ForegroundColor Gray
Write-Host "  Package:       $packagePath" -ForegroundColor Gray
Write-Host "  Webserver:     $webserverPath" -ForegroundColor Gray
Write-Host "  Shared Root:   $sharedRoot" -ForegroundColor Gray
Write-Host "  Verbosity:     $Verbosity" -ForegroundColor Gray

# Create directories
Write-Host "`n[SETUP] Creating environment structure..." -ForegroundColor Cyan
New-Item -Path $baseTemp -ItemType Directory -Force | Out-Null
New-Item -Path $webserverParent -ItemType Directory -Force | Out-Null
New-Item -Path $sharedRoot -ItemType Directory -Force | Out-Null
if ($ExpandArchive) {
    New-Item -Path $extractPath -ItemType Directory -Force | Out-Null
}

# ============================================================================
# DOWNLOAD RELEASE
# ============================================================================

Write-Host "`n[DOWNLOAD] Fetching release from GitHub..." -ForegroundColor Cyan

try {
    Write-Host "  Downloading: $ReleaseUrl" -ForegroundColor Gray
    $stopwatch = [System.Diagnostics.Stopwatch]::StartNew()
    Invoke-WebRequest -Uri $ReleaseUrl -OutFile $downloadPath -ErrorAction Stop
    $stopwatch.Stop()
    $sizeMB = [math]::Round((Get-Item $downloadPath).Length / 1MB, 2)
    Write-Host "  ✓ Downloaded ($sizeMB MB in $([math]::Round($stopwatch.Elapsed.TotalSeconds, 2))s)" -ForegroundColor Green
} catch {
    Write-Host "  ✗ Failed to download release: $_" -ForegroundColor Red
    if (-not $NoCleanup) { Remove-Item -Path $baseTemp -Recurse -Force -ErrorAction SilentlyContinue }
    exit 1
}

# ============================================================================
# EXTRACT RELEASE (optional)
# ============================================================================

if ($ExpandArchive) {
    Write-Host "`n[EXTRACT] Extracting release archive..." -ForegroundColor Cyan
    
    try {
        Write-Host "  Extracting to: $extractPath" -ForegroundColor Gray
        $stopwatch = [System.Diagnostics.Stopwatch]::StartNew()
        Expand-Archive -Path $downloadPath -DestinationPath $extractPath -Force -ErrorAction Stop
        $stopwatch.Stop()
        Write-Host "  ✓ Extracted in $([math]::Round($stopwatch.Elapsed.TotalSeconds, 2))s" -ForegroundColor Green
        
        if (-not (Test-Path $packagePath)) {
            throw "Package path not found at expected location: $packagePath"
        }
    } catch {
        Write-Host "  ✗ Failed to extract release: $_" -ForegroundColor Red
        if (-not $NoCleanup) { Remove-Item -Path $baseTemp -Recurse -Force -ErrorAction SilentlyContinue }
        exit 1
    }
}

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
    $dbPassword = ConvertTo-SecureString "testpassword123" -AsPlainText -Force
    $dbCredential = New-Object System.Management.Automation.PSCredential("app", $dbPassword)

    # Get PHP path
    $phpPath = if (Test-Path "e:\xampp\php\php.exe") {
        "e:\xampp\php\php.exe"
    } else {
        $phpCmd = Get-Command php -ErrorAction SilentlyContinue
        if ($phpCmd) {
            $phpCmd.Source
        } else {
            throw "PHP not found in system PATH"
        }
    }

    Write-Host "Using PHP: $phpPath" -ForegroundColor Gray

    # Run deployment - pass ZIP file directly to module
    $deploymentParams = @{
        DeploymentPackagePath = $downloadPath
        WebserverPath         = $webserverPath
        SharedStorageRoot     = $sharedRoot
        PhpPath               = $phpPath
        AppUrl                = "http://localhost:8000"
        AppName               = "E2E Test Inventory App"
        AppEnv                = "staging"
        AppKey                = "base64:e2etestkey1234567890abcdef=="
        DatabaseCredential    = $dbCredential
        DatabaseHost          = "127.0.0.1"
        DatabasePort          = 3306
        DatabaseName          = "test_inventory_e2e"
        DatabaseSslMode       = "prefer"
        KeepStagingCount      = 3
        InformationAction     = if ($Verbosity -in @('Information', 'Verbose', 'Debug')) { 'Continue' } else { 'SilentlyContinue' }
        Verbose               = ($Verbosity -in @('Verbose', 'Debug'))
    }

    $deploymentStopwatch = [System.Diagnostics.Stopwatch]::StartNew()
    $success = Deploy-Application @deploymentParams
    $deploymentStopwatch.Stop()

    if (-not $success) {
        Write-Host "`n✗ Deployment failed" -ForegroundColor Red
        if (-not $NoCleanup) { Remove-Item -Path $baseTemp -Recurse -Force -ErrorAction SilentlyContinue }
        exit 1
    }

    Write-Host "`n  Deployment completed in $([math]::Round($deploymentStopwatch.Elapsed.TotalSeconds, 2))s" -ForegroundColor Gray

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

# 6. Check vendor directory (composer install was run)
$vendorPath = Join-Path $webserverPath 'vendor'
if (Test-Path $vendorPath) {
    $vendorCount = @(Get-ChildItem -Path $vendorPath -Directory).Length
    Write-Host "  ✓ Composer dependencies installed ($vendorCount packages)" -ForegroundColor Green
} else {
    Write-Host "  ✗ Vendor directory missing (composer install may have failed)" -ForegroundColor Red
    $verificationsOk = $false
}

# 7. Check bootstrap cache (laravel caching was run)
$bootstrapCachePath = Join-Path $webserverPath 'bootstrap' 'cache'
if (Test-Path $bootstrapCachePath) {
    $cacheFiles = @(Get-ChildItem -Path $bootstrapCachePath -File -ErrorAction SilentlyContinue).Length
    Write-Host "  ✓ Bootstrap cache created ($cacheFiles cache files)" -ForegroundColor Green
} else {
    Write-Host "  ✗ Bootstrap cache missing (artisan cache commands may have failed)" -ForegroundColor Red
    $verificationsOk = $false
}

# ============================================================================
# SUMMARY
# ============================================================================

Write-Host "`n╔════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
if ($verificationsOk) {
    Write-Host "║   ✓ E2E DEPLOYMENT TEST PASSED                            ║" -ForegroundColor Green
} else {
    Write-Host "║   ✗ E2E DEPLOYMENT TEST FAILED - VERIFICATION ERRORS      ║" -ForegroundColor Red
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


Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'
$ProgressPreference = 'SilentlyContinue'

Write-Host "`n╔════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║   E2E DEPLOYMENT TEST - Real Release from GitHub           ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan

# ============================================================================
# SETUP
# ============================================================================

$timestamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$baseTemp = Join-Path $env:TEMP "inventory-app-e2e-deploy-test-$timestamp"
$downloadPath = Join-Path $baseTemp 'release.zip'
$extractPath = Join-Path $baseTemp 'extract'
$packagePath = Join-Path $extractPath 'inventory-app-6.0.1'
$webserverParent = Join-Path $baseTemp 'wwwroot'
$webserverPath = Join-Path $webserverParent 'inventory-app'
$sharedRoot = Join-Path $baseTemp 'github-apps'

Write-Host "`nTest Environment:" -ForegroundColor Yellow
Write-Host "  Base:          $baseTemp" -ForegroundColor Gray
Write-Host "  Release URL:   $ReleaseUrl" -ForegroundColor Gray
Write-Host "  Package:       $packagePath" -ForegroundColor Gray
Write-Host "  Webserver:     $webserverPath" -ForegroundColor Gray
Write-Host "  Shared Root:   $sharedRoot" -ForegroundColor Gray

# Create directories
Write-Host "`n[SETUP] Creating environment structure..." -ForegroundColor Cyan
New-Item -Path $baseTemp -ItemType Directory -Force | Out-Null
New-Item -Path $extractPath -ItemType Directory -Force | Out-Null
New-Item -Path $webserverParent -ItemType Directory -Force | Out-Null
New-Item -Path $sharedRoot -ItemType Directory -Force | Out-Null

# ============================================================================
# DOWNLOAD RELEASE
# ============================================================================

Write-Host "`n[DOWNLOAD] Fetching release from GitHub..." -ForegroundColor Cyan

try {
    Write-Host "  Downloading: $ReleaseUrl" -ForegroundColor Gray
    $stopwatch = [System.Diagnostics.Stopwatch]::StartNew()
    Invoke-WebRequest -Uri $ReleaseUrl -OutFile $downloadPath -ErrorAction Stop
    $stopwatch.Stop()
    $sizeMB = [math]::Round((Get-Item $downloadPath).Length / 1MB, 2)
    Write-Host "  ✓ Downloaded ($sizeMB MB in $([math]::Round($stopwatch.Elapsed.TotalSeconds, 2))s)" -ForegroundColor Green
} catch {
    Write-Host "  ✗ Failed to download release: $_" -ForegroundColor Red
    if (-not $NoCleanup) { Remove-Item -Path $baseTemp -Recurse -Force -ErrorAction SilentlyContinue }
    exit 1
}

# ============================================================================
# EXTRACT RELEASE
# ============================================================================

Write-Host "`n[EXTRACT] Extracting release archive..." -ForegroundColor Cyan

try {
    Write-Host "  Extracting to: $extractPath" -ForegroundColor Gray
    Expand-Archive -Path $downloadPath -DestinationPath $extractPath -Force -ErrorAction Stop
    Write-Host "  ✓ Extracted successfully" -ForegroundColor Green
    
    if (-not (Test-Path $packagePath)) {
        throw "Package path not found at expected location: $packagePath"
    }
} catch {
    Write-Host "  ✗ Failed to extract release: $_" -ForegroundColor Red
    if (-not $NoCleanup) { Remove-Item -Path $baseTemp -Recurse -Force -ErrorAction SilentlyContinue }
    exit 1
}

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
    $dbPassword = ConvertTo-SecureString "testpassword123" -AsPlainText -Force
    $dbCredential = New-Object System.Management.Automation.PSCredential("app", $dbPassword)

    # Get PHP path
    $phpPath = if (Test-Path "e:\xampp\php\php.exe") {
        "e:\xampp\php\php.exe"
    } else {
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
        AppName               = "E2E Test Inventory App"
        AppEnv                = "staging"
        AppKey                = "base64:e2etestkey1234567890abcdef=="
        DatabaseCredential    = $dbCredential
        DatabaseHost          = "127.0.0.1"
        DatabasePort          = 3306
        DatabaseName          = "test_inventory_e2e"
        DatabaseSslMode       = "prefer"
        KeepStagingCount      = 3
    }

    $deploymentStopwatch = [System.Diagnostics.Stopwatch]::StartNew()
    $success = Deploy-Application @deploymentParams
    $deploymentStopwatch.Stop()

    if (-not $success) {
        Write-Host "`n✗ Deployment failed" -ForegroundColor Red
        if (-not $NoCleanup) { Remove-Item -Path $baseTemp -Recurse -Force -ErrorAction SilentlyContinue }
        exit 1
    }

    Write-Host "`n  Deployment completed in $($deploymentStopwatch.Elapsed.TotalSeconds)s" -ForegroundColor Gray

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

# 6. Check vendor directory (composer install was run)
$vendorPath = Join-Path $webserverPath 'vendor'
if (Test-Path $vendorPath) {
    $vendorCount = @(Get-ChildItem -Path $vendorPath -Directory).Length
    Write-Host "  ✓ Composer dependencies installed ($vendorCount packages)" -ForegroundColor Green
} else {
    Write-Host "  ✗ Vendor directory missing (composer install may have failed)" -ForegroundColor Red
    $verificationsOk = $false
}

# 7. Check bootstrap cache (laravel caching was run)
$bootstrapCachePath = Join-Path $webserverPath 'bootstrap' 'cache'
if (Test-Path $bootstrapCachePath) {
    $cacheFiles = @(Get-ChildItem -Path $bootstrapCachePath -File -ErrorAction SilentlyContinue).Length
    Write-Host "  ✓ Bootstrap cache created ($cacheFiles cache files)" -ForegroundColor Green
} else {
    Write-Host "  ✗ Bootstrap cache missing (artisan cache commands may have failed)" -ForegroundColor Red
    $verificationsOk = $false
}

# ============================================================================
# SUMMARY
# ============================================================================

Write-Host "`n╔════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
if ($verificationsOk) {
    Write-Host "║   ✓ E2E DEPLOYMENT TEST PASSED                             ║" -ForegroundColor Green
} else {
    Write-Host "║   ✗ E2E DEPLOYMENT TEST FAILED - VERIFICATION ERRORS       ║" -ForegroundColor Red
}
Write-Host "╚═════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan

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
