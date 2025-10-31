#Requires -Version 5.0

<#
.SYNOPSIS
    Local test of continuous deployment workflow steps
    
.DESCRIPTION
    Executes each step from continuous-deployment.yml workflow locally
    for testing purposes using tag v6.0.1
    
.EXAMPLE
    .\scripts\tests\test-cd-workflow-local.ps1 -Verbose
#>

param(
    [switch] $SkipDownload,
    [switch] $SkipExtract,
    [switch] $SkipValidate,
    [switch] $SkipDeploy,
    [switch] $SkipHealth
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'
$ProgressPreference = 'SilentlyContinue'

Write-Host "`n╔════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║   CD WORKFLOW - LOCAL TEST                                  ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan

# ============================================================================
# CONFIGURATION
# ============================================================================

$tag = 'v6.0.1'
$owner = 'metanull'
$repo = 'inventory-app'

# Tag archive URL (public, no token needed)
$archiveUrl = "https://github.com/$owner/$repo/archive/refs/tags/$tag.zip"

# Create random temp directory
$tempDir = Join-Path $env:TEMP "cd-workflow-test-$(Get-Random)"
$logFile = Join-Path $tempDir 'workflow-test.log'

Write-Host "`n📁 Test Environment:" -ForegroundColor Yellow
Write-Host "   Tag: $tag"
Write-Host "   URL: $archiveUrl"
Write-Host "   Temp Dir: $tempDir"
Write-Host "   Log File: $logFile"

New-Item -Path $tempDir -ItemType Directory -Force | Out-Null
New-Item -Path $logFile -ItemType File -Force | Out-Null

function Write-Log {
    param([string] $Message)
    Add-Content -Path $logFile -Value $Message
    Write-Host $Message
}

Write-Log "=== CD Workflow Local Test ==="
Write-Log "Started: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
Write-Log "Tag: $tag"
Write-Log ""

# ============================================================================
# STEP 1: Determine Release Information
# ============================================================================

Write-Host "`n[STEP 1] Determine Release Information" -ForegroundColor Cyan
Write-Log "[STEP 1] Determine Release Information"

try {
    $releaseTag = $tag
    Write-Host "   ✓ Tag: $releaseTag"
    Write-Log "   ✓ Tag: $releaseTag"
} catch {
    Write-Error "Failed to determine release tag: $_"
    exit 1
}

# ============================================================================
# STEP 2: Get Archive URL
# ============================================================================

Write-Host "`n[STEP 2] Prepare Archive URL" -ForegroundColor Cyan
Write-Log "[STEP 2] Prepare Archive URL"

try {
    Write-Host "   ✓ URL: $archiveUrl"
    Write-Log "   ✓ URL: $archiveUrl"
} catch {
    Write-Error "Failed to prepare archive URL: $_"
    exit 1
}

# ============================================================================
# STEP 3: Download Release Archive
# ============================================================================

Write-Host "`n[STEP 3] Download Archive from GitHub" -ForegroundColor Cyan
Write-Log "[STEP 3] Download Archive from GitHub"

try {
    $zipPath = Join-Path $tempDir "inventory-app-$tag.zip"
    Write-Host "   Downloading to: $zipPath"
    Write-Log "   Downloading to: $zipPath"
    
    $stopwatch = [System.Diagnostics.Stopwatch]::StartNew()
    
    Invoke-WebRequest -Uri $archiveUrl -OutFile $zipPath -UseBasicParsing
    
    $stopwatch.Stop()
    $sizeMB = [math]::Round((Get-Item $zipPath).Length / 1MB, 2)
    $seconds = [math]::Round($stopwatch.Elapsed.TotalSeconds, 2)
    
    Write-Host "   ✓ Downloaded ($sizeMB MB in ${seconds}s)"
    Write-Log "   ✓ Downloaded ($sizeMB MB in ${seconds}s)"
    
} catch {
    Write-Error "Failed to download archive: $_"
    exit 1
}

# ============================================================================
# STEP 4: Extract Release Package
# ============================================================================

if (-not $SkipExtract) {
    Write-Host "`n[STEP 4] Extract Release Package" -ForegroundColor Cyan
    Write-Log "[STEP 4] Extract Release Package"
    
    try {
        if (-not (Test-Path $zipPath)) {
            Write-Error "ZIP file not found: $zipPath"
            exit 1
        }
        
        $extractPath = Join-Path $tempDir 'release'
        Write-Host "   Extracting to: $extractPath"
        Write-Log "   Extracting to: $extractPath"
        
        Expand-Archive -Path $zipPath -DestinationPath $extractPath -Force
        Write-Host "   ✓ Extracted successfully"
        Write-Log "   ✓ Extracted successfully"
        
        # Find the actual app directory (tag archive creates inventory-app-<tag>)
        $appDirs = @(Get-ChildItem -Path $extractPath -Directory | 
            Where-Object { Test-Path (Join-Path $_.FullName 'artisan') })
        
        if ($appDirs.Count -eq 0) {
            Write-Error "No Laravel app directory found in archive"
            exit 1
        }
        
        if ($appDirs.Count -gt 1) {
            Write-Error "Multiple Laravel app directories found, expected 1"
            exit 1
        }
        
        $packagePath = $appDirs[0].FullName
        Write-Host "   ✓ App directory: $(Split-Path $packagePath -Leaf)"
        Write-Log "   ✓ App directory: $(Split-Path $packagePath -Leaf)"
        
    } catch {
        Write-Error "Failed to extract archive: $_"
        exit 1
    }
}

# ============================================================================
# STEP 5: Validate Deployment Package
# ============================================================================

if (-not $SkipValidate) {
    Write-Host "`n[STEP 5] Validate Deployment Package" -ForegroundColor Cyan
    Write-Log "[STEP 5] Validate Deployment Package"
    
    try {
        if (-not (Test-Path $packagePath)) {
            Write-Error "Package path not found: $packagePath"
            exit 1
        }
        
        $requiredFiles = @('artisan', 'composer.json', '.env.example', 'routes/api.php')
        $requiredDirs = @('app', 'database', 'routes', 'resources', 'public')
        
        Write-Host "   Checking required files..."
        Write-Log "   Checking required files..."
        foreach ($file in $requiredFiles) {
            $filePath = Join-Path $packagePath $file
            if (-not (Test-Path $filePath)) {
                Write-Error "Required file not found: $file"
                exit 1
            }
            Write-Host "      ✓ $file"
            Write-Log "      ✓ $file"
        }
        
        Write-Host "   Checking required directories..."
        Write-Log "   Checking required directories..."
        foreach ($dir in $requiredDirs) {
            $dirPath = Join-Path $packagePath $dir
            if (-not (Test-Path $dirPath -PathType Container)) {
                Write-Error "Required directory not found: $dir"
                exit 1
            }
            Write-Host "      ✓ $dir/"
            Write-Log "      ✓ $dir/"
        }
        
        Write-Host "   ✓ Package structure validated"
        Write-Log "   ✓ Package structure validated"
        
    } catch {
        Write-Error "Failed to validate package: $_"
        exit 1
    }
}

# ============================================================================
# STEP 6: Deploy Application (Simulation)
# ============================================================================

if (-not $SkipDeploy) {
    Write-Host "`n[STEP 6] Deploy Application" -ForegroundColor Cyan
    Write-Log "[STEP 6] Deploy Application"
    
    try {
        if (-not (Test-Path $packagePath)) {
            Write-Error "Package path not found: $packagePath"
            exit 1
        }
        
        $deploymentScript = Join-Path (Get-Location) 'scripts\Deploy-Application.ps1'
        
        if (-not (Test-Path $deploymentScript)) {
            Write-Error "Deploy script not found: $deploymentScript"
            Write-Host "   ⚠️  Skipping actual deployment (script not in expected location)"
            Write-Log "   ⚠️  Skipping actual deployment (script not in expected location)"
        } else {
            Write-Host "   Found deployment script: $deploymentScript"
            Write-Log "   Found deployment script: $deploymentScript"
            
            # Note: Not executing actual deployment to avoid modifying production environment
            Write-Host "   ⚠️  Skipping actual deployment execution (local test only)"
            Write-Log "   ⚠️  Skipping actual deployment execution (local test only)"
            Write-Host "   To run deployment: "
            Write-Host "     `$dbPassword = ConvertTo-SecureString 'password' -AsPlainText -Force"
            Write-Host "     `$credential = New-Object PSCredential ('app', `$dbPassword)"
            Write-Host ""
            Write-Host "     & '$deploymentScript' ``"
            Write-Host "       -DeploymentPackagePath '$packagePath' ``"
            Write-Host "       -WebserverPath 'C:\Apache24\htdocs\inventory-app' ``"
            Write-Host "       -SharedStorageRoot 'C:\mwnf-server\github-apps' ``"
            Write-Host "       -PhpPath 'C:\php\php.exe' ``"
            Write-Host "       -AppUrl 'https://inventory.local' ``"
            Write-Host "       -AppName 'Inventory App' ``"
            Write-Host "       -AppEnv 'production' ``"
            Write-Host "       -AppKey 'base64:xxxxx...' ``"
            Write-Host "       -DatabaseHost '127.0.0.1' ``"
            Write-Host "       -DatabasePort 3306 ``"
            Write-Host "       -DatabaseName 'inventory_db' ``"
            Write-Host "       -DatabaseCredential `$credential ``"
            Write-Host "       -Verbose"
            
            Write-Log "   ⚠️  Skipping actual deployment execution (local test only)"
        }
        
    } catch {
        Write-Error "Failed in deployment step: $_"
        exit 1
    }
}

# ============================================================================
# STEP 7: Verify Deployment Health (Simulation)
# ============================================================================

if (-not $SkipHealth) {
    Write-Host "`n[STEP 7] Verify Deployment Health" -ForegroundColor Cyan
    Write-Log "[STEP 7] Verify Deployment Health"
    
    try {
        Write-Host "   ⚠️  Skipping health check (app not deployed in local test)"
        Write-Log "   ⚠️  Skipping health check (app not deployed in local test)"
        Write-Host "   Health check would test: https://inventory.local/health"
        Write-Log "   Health check would test: https://inventory.local/health"
        
    } catch {
        Write-Error "Failed in health check step: $_"
        exit 1
    }
}

# ============================================================================
# SUMMARY
# ============================================================================

Write-Host "`n╔════════════════════════════════════════════════════════════╗" -ForegroundColor Green
Write-Host "║   ✓ WORKFLOW TEST COMPLETED SUCCESSFULLY                   ║" -ForegroundColor Green
Write-Host "╚════════════════════════════════════════════════════════════╝" -ForegroundColor Green

Write-Host "`n📊 Test Results:" -ForegroundColor Yellow
Write-Host "   Downloaded: $assetName"
Write-Host "   Extracted: $(Split-Path $packagePath -Leaf)"
Write-Host "   Validated: Package structure intact"
Write-Host "   Logs: $logFile"

Write-Log ""
Write-Log "=== Test Completed Successfully ==="
Write-Log "Ended: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"

Write-Host "`n📝 Full log saved to: $logFile`n"
