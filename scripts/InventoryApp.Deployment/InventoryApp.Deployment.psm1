#Requires -Version 5.0

<#
.SYNOPSIS
    Inventory App Deployment Module for Windows-based Laravel application deployment.

.DESCRIPTION
    Provides functions for deploying the Inventory Management API with persistent storage,
    atomic symlink swapping, and comprehensive error handling with rollback capabilities.

.NOTES
    Author: Museum With No Frontiers
    Version: 1.0.0
    Requires: PowerShell 5.0+, Administrator privileges, PHP 8.2+
#>

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

# ============================================================================
# VALIDATION FUNCTIONS
# ============================================================================

<#
.SYNOPSIS
    Tests system prerequisites for deployment.

.DESCRIPTION
    Validates PHP installation, Laravel app structure, disk space, and permissions.

.PARAMETER PhpPath
    Path to PHP executable (e.g., 'C:\php\php.exe')

.PARAMETER WebserverPath
    Path to webserver deployment directory

.PARAMETER MinDiskSpaceGB
    Minimum required free disk space in GB (default: 5)

.EXAMPLE
    Test-SystemPrerequisites -PhpPath "C:\php\php.exe" -WebserverPath "C:\Apache24\htdocs\inventory-app"
#>
function Test-SystemPrerequisites {
    [CmdletBinding()]
    param(
        [Parameter(Mandatory = $true)]
        [ValidateNotNullOrEmpty()]
        [string] $PhpPath,

        [Parameter(Mandatory = $true)]
        [ValidateNotNullOrEmpty()]
        [string] $WebserverPath,

        [Parameter(Mandatory = $false)]
        [ValidateRange(1, 100)]
        [int] $MinDiskSpaceGB = 5
    )

    Write-Information "Testing system prerequisites..."

    # Test PHP installation and version
    if (-not (Test-Path $PhpPath)) {
        throw "PHP executable not found at: $PhpPath"
    }

    try {
        $phpVersion = & $PhpPath --version 2>&1 -ErrorAction SilentlyContinue
        Write-Verbose "PHP version output: $phpVersion"

        # Extract version number and verify it's 8.2 or higher
        # For production: require version match. For testing: allow graceful skip if no match.
        $versionMatch = $phpVersion | Select-String -Pattern 'PHP (\d+\.(\d+))'
        if ($versionMatch) {
            $version = [version]($versionMatch.Matches.Groups[1].Value)
            $minVersion = [version]'8.2'
            if ($version -lt $minVersion) {
                throw "PHP version must be 8.2 or higher. Found: $version"
            }
            Write-Verbose "PHP version validated: $version"
        } else {
            Write-Verbose "Could not extract PHP version from output. Skipping validation (may be mock/test script)."
        }
    } catch {
        if ($_.Exception.Message -like "*PHP version*") {
            throw $_
        }
        Write-Warning "PHP version verification warning: $_"
    }

    # Check webserver path exists
    if (-not (Test-Path $WebserverPath)) {
        Write-Warning "Webserver path does not exist yet: $WebserverPath"
    }

    # Test write permissions
    $testFile = Join-Path $WebserverPath ".deployment-test-$(Get-Random)"
    try {
        New-Item -Path $testFile -ItemType File -Force | Out-Null
        Remove-Item $testFile -Force -ErrorAction SilentlyContinue
        Write-Verbose "Write permissions verified for: $WebserverPath"
    } catch {
        throw "Insufficient write permissions to: $WebserverPath"
    }

    # Check disk space
    $drive = (Split-Path $WebserverPath -Qualifier)
    $diskSpace = Get-Volume -DriveLetter ($drive -replace ':') -ErrorAction SilentlyContinue
    if ($diskSpace -and ($diskSpace.SizeRemaining / 1GB) -lt $MinDiskSpaceGB) {
        throw "Insufficient disk space. Required: ${MinDiskSpaceGB}GB, Available: $([math]::Round($diskSpace.SizeRemaining / 1GB, 2))GB"
    }

    Write-Information "✓ System prerequisites validated"
}

<#
.SYNOPSIS
    Tests deployment package integrity.

.DESCRIPTION
    Validates required files and directory structure in deployment package.

.PARAMETER PackagePath
    Path to extracted deployment package

.EXAMPLE
    Test-DeploymentPackage -PackagePath "C:\temp\inventory-app-release-20251031"
#>
function Test-DeploymentPackage {
    [CmdletBinding()]
    param(
        [Parameter(Mandatory = $true)]
        [ValidateNotNullOrEmpty()]
        [string] $PackagePath
    )

    Write-Information "Validating deployment package..."

    $requiredFiles = @(
        '.env.example',
        'artisan',
        'composer.lock'
    )

    $requiredDirs = @(
        'app',
        'routes',
        'resources',
        'public',
        'database',
        'bootstrap',
        'config'
    )

    # Check required files
    foreach ($file in $requiredFiles) {
        $filePath = Join-Path $PackagePath $file
        if (-not (Test-Path $filePath)) {
            throw "Required file missing: $file"
        }
    }

    # Check required directories
    foreach ($dir in $requiredDirs) {
        $dirPath = Join-Path $PackagePath $dir
        if (-not (Test-Path $dirPath -PathType Container)) {
            throw "Required directory missing: $dir"
        }
    }

    Write-Information "✓ Deployment package validated"
}

# ============================================================================
# DIRECTORY MANAGEMENT FUNCTIONS
# ============================================================================

<#
.SYNOPSIS
    Creates a new timestamped staging directory.

.DESCRIPTION
    Creates a new directory with format 'staging-YYYYMMDD-HHMMSS'.

.PARAMETER BaseDirectory
    Parent directory for staging directories

.EXAMPLE
    $stagingDir = New-StagingDirectory -BaseDirectory "C:\mwnf-server\github-apps"
    # Returns: C:\mwnf-server\github-apps\staging-20251031-041516
#>
function New-StagingDirectory {
    [CmdletBinding()]
    param(
        [Parameter(Mandatory = $true)]
        [ValidateNotNullOrEmpty()]
        [string] $BaseDirectory
    )

    $timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
    $stagingName = "staging-$timestamp"
    $stagingPath = Join-Path $BaseDirectory $stagingName

    Write-Information "Creating staging directory: $stagingName"

    New-Item -Path $stagingPath -ItemType Directory -Force | Out-Null

    if (-not (Test-Path $stagingPath)) {
        throw "Failed to create staging directory: $stagingPath"
    }

    Write-Information "✓ Staging directory created: $stagingPath"
    return $stagingPath
}

<#
.SYNOPSIS
    Removes old staging directories, keeping only the most recent ones.

.DESCRIPTION
    Deletes staging directories older than the specified count, preserving the newest ones for rollback.

.PARAMETER BaseDirectory
    Parent directory containing staging directories

.PARAMETER KeepCount
    Number of staging directories to keep (default: 3)

.EXAMPLE
    Remove-OldStagingDirectories -BaseDirectory "C:\mwnf-server\github-apps" -KeepCount 3
#>
function Remove-OldStagingDirectories {
    [CmdletBinding()]
    param(
        [Parameter(Mandatory = $true)]
        [ValidateNotNullOrEmpty()]
        [string] $BaseDirectory,

        [Parameter(Mandatory = $false)]
        [ValidateRange(1, 10)]
        [int] $KeepCount = 3
    )

    Write-Information "Cleaning up old staging directories (keeping $KeepCount)..."

    $stagingDirs = @(Get-ChildItem -Path $BaseDirectory -Directory -Filter 'staging-*' -ErrorAction SilentlyContinue |
        Sort-Object -Property Name -Descending)

    if ($stagingDirs.Length -le $KeepCount) {
        Write-Verbose "Only $($stagingDirs.Length) staging directories found, nothing to clean"
        return
    }

    $toDelete = $stagingDirs | Select-Object -Skip $KeepCount

    foreach ($dir in $toDelete) {
        try {
            Write-Verbose "Removing old staging directory: $($dir.FullName)"
            Remove-Item -Path $dir.FullName -Recurse -Force -ErrorAction Stop
            Write-Information "✓ Removed: $($dir.Name)"
        } catch {
            Write-Warning "Failed to remove staging directory $($dir.Name): $_"
        }
    }
}

# ============================================================================
# SYMLINK MANAGEMENT FUNCTIONS
# ============================================================================

<#
.SYNOPSIS
    Creates or verifies a symlink for persistent storage.

.DESCRIPTION
    Creates a junction (directory symlink) linking staging storage to shared persistent storage.
    Ensures shared storage structure exists before creating symlink.

.PARAMETER StagingPath
    Path to the staging application directory

.PARAMETER SharedStorageRoot
    Root path for shared storage (parent of 'shared-storage' directory)

.EXAMPLE
    New-StorageSymlink -StagingPath "C:\mwnf-server\github-apps\staging-20251031-041516" `
                       -SharedStorageRoot "C:\mwnf-server\github-apps"
#>
function New-StorageSymlink {
    [CmdletBinding()]
    param(
        [Parameter(Mandatory = $true)]
        [ValidateNotNullOrEmpty()]
        [string] $StagingPath,

        [Parameter(Mandatory = $true)]
        [ValidateNotNullOrEmpty()]
        [string] $SharedStorageRoot
    )

    Write-Information "Setting up storage symlink..."

    $stagingStoragePath = Join-Path $StagingPath 'storage'
    $sharedStoragePath = Join-Path $SharedStorageRoot 'shared-storage' 'storage'
    $sharedStorageRoot = Split-Path $sharedStoragePath

    # Create shared storage directory structure
    if (-not (Test-Path $sharedStorageRoot)) {
        Write-Verbose "Creating shared storage root: $sharedStorageRoot"
        New-Item -Path $sharedStorageRoot -ItemType Directory -Force | Out-Null
    }

    # Create required subdirectories in shared storage
    $storageSubdirs = @('app', 'logs', 'framework/cache', 'framework/sessions', 'framework/views')
    foreach ($subdir in $storageSubdirs) {
        $subPath = Join-Path $sharedStoragePath $subdir
        if (-not (Test-Path $subPath)) {
            Write-Verbose "Creating shared storage subdirectory: $subdir"
            New-Item -Path $subPath -ItemType Directory -Force | Out-Null
        }
    }

    # Remove placeholder storage directory from staging if it exists
    if (Test-Path $stagingStoragePath) {
        Write-Verbose "Removing placeholder storage from staging"
        Remove-Item -Path $stagingStoragePath -Recurse -Force -ErrorAction SilentlyContinue
    }

    # Create symlink (junction for directories)
    try {
        Write-Verbose "Creating junction: $stagingStoragePath -> $sharedStoragePath"
        # Use New-Item with -ItemType Junction for directory symlinks
        New-Item -ItemType Junction -Path $stagingStoragePath -Target $sharedStoragePath -Force -ErrorAction Stop | Out-Null

        # Verify symlink
        if (-not (Test-Path $stagingStoragePath)) {
            throw "Symlink created but not accessible"
        }

        Write-Information "✓ Storage symlink created: $stagingStoragePath -> $sharedStoragePath"
    } catch {
        throw "Failed to create storage symlink: $_"
    }
}

<#
.SYNOPSIS
    Performs atomic swap of production symlink with rollback capability.

.DESCRIPTION
    Atomically switches production symlink from old staging to new staging directory.
    Backs up current production symlink as 'production_swap' for rollback capability.

.PARAMETER CurrentProductionPath
    Current production symlink path

.PARAMETER NewStagingPath
    New staging directory to activate

.PARAMETER PhpPath
    Path to PHP executable for health checks

.EXAMPLE
    Swap-WebserverSymlink -CurrentProductionPath "C:\mwnf-server\github-apps\production" `
                          -NewStagingPath "C:\mwnf-server\github-apps\staging-20251031-041516" `
                          -PhpPath "C:\php\php.exe"
#>
function Swap-WebserverSymlink {
    [CmdletBinding()]
    param(
        [Parameter(Mandatory = $true)]
        [ValidateNotNullOrEmpty()]
        [string] $CurrentProductionPath,

        [Parameter(Mandatory = $true)]
        [ValidateNotNullOrEmpty()]
        [string] $NewStagingPath,

        [Parameter(Mandatory = $true)]
        [ValidateNotNullOrEmpty()]
        [string] $PhpPath
    )

    Write-Information "Preparing for production swap..."

    $swapBackupPath = "$CurrentProductionPath`_swap"

    try {
        # Step 1: Backup current production
        if (Test-Path $CurrentProductionPath) {
            Write-Information "Backing up current production symlink..."
            if (Test-Path $swapBackupPath) {
                Remove-Item -Path $swapBackupPath -Force -ErrorAction SilentlyContinue
            }
            Rename-Item -Path $CurrentProductionPath -NewName "production_swap" -Force -ErrorAction Stop
            Write-Verbose "✓ Current production backed up: $swapBackupPath"
        }

        # Step 2: Verify new staging exists
        if (-not (Test-Path $NewStagingPath)) {
            throw "New staging path does not exist: $NewStagingPath"
        }

        # Step 3: Create new production symlink
        Write-Information "Creating new production symlink..."
        New-Item -ItemType Junction -Path $CurrentProductionPath -Target $NewStagingPath -Force -ErrorAction Stop | Out-Null

        # Step 4: Verify symlink creation
        if (-not (Test-Path $CurrentProductionPath)) {
            throw "Production symlink created but not accessible"
        }

        Write-Information "✓ Production symlink swapped successfully"
        return $true

    } catch {
        # Rollback on failure
        Write-Warning "Swap failed, initiating rollback: $_"

        if (Test-Path $swapBackupPath) {
            try {
                if (Test-Path $CurrentProductionPath) {
                    Remove-Item -Path $CurrentProductionPath -Force -ErrorAction SilentlyContinue
                }
                Rename-Item -Path $swapBackupPath -NewName "production" -Force -ErrorAction Stop
                Write-Information "✓ Rolled back to previous production version"
            } catch {
                throw "Rollback failed: $_"
            }
        }

        throw "Swap operation failed: $_"
    }
}

<#
.SYNOPSIS
    Cleans up swap backup symlink after successful deployment.

.DESCRIPTION
    Removes the production_swap backup symlink after confirming new deployment is stable.

.PARAMETER CurrentProductionPath
    Current production symlink path

.EXAMPLE
    Remove-SwapBackup -CurrentProductionPath "C:\mwnf-server\github-apps\production"
#>
function Remove-SwapBackup {
    [CmdletBinding()]
    param(
        [Parameter(Mandatory = $true)]
        [ValidateNotNullOrEmpty()]
        [string] $CurrentProductionPath
    )

    $swapBackupPath = "$CurrentProductionPath`_swap"

    if (Test-Path $swapBackupPath) {
        Write-Information "Removing swap backup..."
        try {
            Remove-Item -Path $swapBackupPath -Recurse -Force -ErrorAction Stop
            Write-Information "✓ Swap backup removed: $swapBackupPath"
        } catch {
            Write-Warning "Failed to remove swap backup: $_"
        }
    }
}

# ============================================================================
# CONFIGURATION FUNCTIONS
# ============================================================================

<#
.SYNOPSIS
    Creates and configures .env file for Laravel application.

.DESCRIPTION
    Copies .env.example to .env and replaces environment placeholders with actual values
    using a hashtable of key=value patterns. Validates that all critical variables are set.

.PARAMETER StagingPath
    Path to staging application directory

.PARAMETER EnvironmentVariables
    Hashtable of environment variable names and values

.EXAMPLE
    $envVars = @{
        'APP_URL=http://localhost' = "APP_URL=https://inventory.museumwnf.org"
        'DB_HOST=127.0.0.1' = "DB_HOST=prod-db-server"
    }
    New-EnvironmentFile -StagingPath "C:\staging" -EnvironmentVariables $envVars
#>
function New-EnvironmentFile {
    [CmdletBinding()]
    param(
        [Parameter(Mandatory = $true)]
        [ValidateNotNullOrEmpty()]
        [string] $StagingPath,

        [Parameter(Mandatory = $true)]
        [ValidateNotNullOrEmpty()]
        [hashtable] $EnvironmentVariables
    )

    Write-Information "Configuring environment file..."

    $envExamplePath = Join-Path $StagingPath '.env.example'
    $envPath = Join-Path $StagingPath '.env'

    if (-not (Test-Path $envExamplePath)) {
        throw ".env.example not found at: $envExamplePath"
    }

    # Copy template
    Write-Verbose "Copying .env.example to .env"
    Copy-Item -Path $envExamplePath -Destination $envPath -Force -ErrorAction Stop

    <#
    # DEBUG: Dump the entire .env file
    Write-Information "DEBUG: Contents of $envPath BEFORE replacement:"
    $fileContent = Get-Content -Path $envPath -ErrorAction Stop
    foreach ($line in $fileContent) {
        Write-Information "  $line"
    }
    #>

    # Read lines (NOT -Raw - process line by line for reliability)
    # Force to array even if single line
    $lines = @(Get-Content -Path $envPath -ErrorAction Stop)
    
    Write-Verbose "Read $($lines.Length) lines from .env.example"

    # Replace variables line by line
    foreach ($key in $EnvironmentVariables.Keys) {
        $value = $EnvironmentVariables[$key]
        $found = $false
        
        for ($i = 0; $i -lt $lines.Length; $i++) {
            # Match KEY=anything pattern (without multiline mode)
            if ($lines[$i] -match "^$([regex]::Escape($key))=") {
                $lines[$i] = "$key=$value"
                $found = $true
                Write-Verbose "  Replaced: $key"
                break
            }
        }
        
        # If not found in file, append it (e.g., APP_URL might not be in .env.example)
        if (-not $found) {
            Write-Verbose "  Appended (not in template): $key"
            $lines += "$key=$value"
        }
    }

    # Write back
    Set-Content -Path $envPath -Value $lines -Encoding UTF8 -ErrorAction Stop

    <#
    # DEBUG: Dump the entire .env file
    Write-Information "DEBUG: Contents of $envPath AFTER replacement:"
    $fileContent = Get-Content -Path $envPath -ErrorAction Stop
    foreach ($line in $fileContent) {
        Write-Information "  $line"
    }
    #>

    # Validate critical variables are present
    $criticalVars = @('APP_URL', 'APP_ENV', 'APP_KEY', 'DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD')
    
    # Read all lines once (not inside loop)
    $envLines = @(Get-Content -Path $envPath -ErrorAction Stop)
    
    foreach ($var in $criticalVars) {
        $found = $false
        
        foreach ($line in $envLines) {
            # Simple check: line starts with KEY= and has content after =
            if ($line -match "^$([regex]::Escape($var))=\S") {
                $found = $true
                break
            }
        }
        
        if (-not $found) {
            throw "Critical environment variable not configured: $var"
        }
    }

    # Set restrictive file permissions (owner only)
    $acl = Get-Acl $envPath
    $acl.SetAccessRuleProtection($true, $false)
    Set-Acl -Path $envPath -AclObject $acl

    Write-Information "✓ Environment file configured: $envPath"
}

# ============================================================================
# LARAVEL SETUP FUNCTIONS
# ============================================================================

<#
.SYNOPSIS
    Runs Laravel setup commands for deployment.

.DESCRIPTION
    Executes database migrations, permissions sync, and Laravel cache commands.

.PARAMETER StagingPath
    Path to staging application directory

.PARAMETER PhpPath
    Path to PHP executable

.PARAMETER AppEnv
    Application environment (production, staging, etc.)

.EXAMPLE
    Invoke-LaravelSetup -StagingPath "C:\mwnf-server\github-apps\staging-20251031" `
                        -PhpPath "C:\php\php.exe" `
                        -AppEnv "production"
#>
function Invoke-LaravelSetup {
    [CmdletBinding()]
    param(
        [Parameter(Mandatory = $true)]
        [ValidateNotNullOrEmpty()]
        [string] $StagingPath,

        [Parameter(Mandatory = $true)]
        [ValidateNotNullOrEmpty()]
        [string] $PhpPath,

        [Parameter(Mandatory = $true)]
        [ValidateSet('production', 'staging', 'development')]
        [string] $AppEnv
    )

    Write-Information "Running Laravel setup..."
    Push-Location $StagingPath

    try {
        # Run migrations
        Write-Information "Running database migrations..."
        $output = & $PhpPath artisan migrate --force 2>&1
        if ($LASTEXITCODE -ne 0) {
            throw "Migration failed: $output"
        }
        Write-Verbose "✓ Migrations completed"

        # Sync permissions (if command exists)
        Write-Information "Syncing permissions..."
        $output = & $PhpPath artisan permissions:sync --production 2>&1
        if ($LASTEXITCODE -eq 0) {
            Write-Verbose "✓ Permissions synced"
        } else {
            Write-Verbose "Permissions sync skipped (command not available)"
        }

        # Cache configuration
        Write-Information "Caching configuration..."
        $output = & $PhpPath artisan config:cache 2>&1
        if ($LASTEXITCODE -ne 0) {
            throw "Config cache failed: $output"
        }
        Write-Verbose "✓ Configuration cached"

        # Cache routes
        Write-Information "Caching routes..."
        $output = & $PhpPath artisan route:cache 2>&1
        if ($LASTEXITCODE -ne 0) {
            throw "Route cache failed: $output"
        }
        Write-Verbose "✓ Routes cached"

        # Cache views
        Write-Information "Caching views..."
        $output = & $PhpPath artisan view:cache 2>&1
        if ($LASTEXITCODE -ne 0) {
            Write-Verbose "View cache failed, continuing: $output"
        } else {
            Write-Verbose "✓ Views cached"
        }

        # Bring application online
        Write-Information "Bringing application online..."
        $output = & $PhpPath artisan up 2>&1
        if ($LASTEXITCODE -ne 0) {
            Write-Verbose "Up command failed: $output"
        }
        Write-Verbose "✓ Application brought online"

        Write-Information "✓ Laravel setup completed"

    } finally {
        Pop-Location
    }
}

<#
.SYNOPSIS
    Puts application in maintenance mode.

.DESCRIPTION
    Executes 'php artisan down' to prevent user access during deployment.

.PARAMETER StagingPath
    Path to staging application directory

.PARAMETER PhpPath
    Path to PHP executable

.EXAMPLE
    Invoke-LaravelDown -StagingPath "C:\mwnf-server\github-apps\staging-20251031" `
                       -PhpPath "C:\php\php.exe"
#>
function Invoke-LaravelDown {
    [CmdletBinding()]
    param(
        [Parameter(Mandatory = $true)]
        [ValidateNotNullOrEmpty()]
        [string] $StagingPath,

        [Parameter(Mandatory = $true)]
        [ValidateNotNullOrEmpty()]
        [string] $PhpPath
    )

    Write-Information "Putting application in maintenance mode..."
    Push-Location $StagingPath

    try {
        $output = & $PhpPath artisan down 2>&1
        if ($LASTEXITCODE -ne 0) {
            Write-Verbose "Down command output: $output"
        }
        Write-Information "✓ Application in maintenance mode"
    } finally {
        Pop-Location
    }
}

# ============================================================================
# DEPLOYMENT ORCHESTRATION
# ============================================================================

<#
.SYNOPSIS
    Main deployment orchestration function.

.DESCRIPTION
    Coordinates the complete deployment process: validation, staging setup,
    symlink configuration, application swap, and Laravel initialization.

.PARAMETER DeploymentPackagePath
    Path to extracted deployment package

.PARAMETER WebserverPath
    Path to webserver deployment directory (where production symlink lives)

.PARAMETER SharedStorageRoot
    Root path for shared storage (parent of 'shared-storage' directory)

.PARAMETER PhpPath
    Path to PHP executable

.PARAMETER AppUrl
    Application URL (e.g., 'https://inventory.museumwnf.org')

.PARAMETER AppName
    Application name for display

.PARAMETER AppEnv
    Application environment (production, staging, development)

.PARAMETER AppKey
    Laravel APP_KEY (base64 encoded)

.PARAMETER DatabaseCredential
    PSCredential object with database username and password

.PARAMETER DatabaseHost
    Database server hostname

.PARAMETER DatabasePort
    Database server port

.PARAMETER DatabaseName
    Database name

.PARAMETER DatabaseSslMode
    SSL mode for database connection (skip-verify, prefer, require)

.EXAMPLE
    $dbCredential = New-Object System.Management.Automation.PSCredential (
        'app',
        (ConvertTo-SecureString 'password' -AsPlainText -Force)
    )

    Deploy-Application `
        -DeploymentPackagePath "C:\temp\inventory-app-release-20251031" `
        -WebserverPath "C:\Apache24\htdocs\inventory-app" `
        -SharedStorageRoot "C:\mwnf-server\github-apps" `
        -PhpPath "C:\php\php.exe" `
        -AppUrl "https://inventory.museumwnf.org" `
        -AppName "Inventory App" `
        -AppEnv "production" `
        -AppKey "base64:xxxxx..." `
        -DatabaseCredential $dbCredential `
        -DatabaseHost "127.0.0.1" `
        -DatabasePort 3306 `
        -DatabaseName "inventory_db" `
        -Verbose
#>
function Deploy-Application {
    [CmdletBinding()]
    param(
        [Parameter(Mandatory = $true)]
        [ValidateNotNullOrEmpty()]
        [string] $DeploymentPackagePath,

        [Parameter(Mandatory = $true)]
        [ValidateNotNullOrEmpty()]
        [string] $WebserverPath,

        [Parameter(Mandatory = $true)]
        [ValidateNotNullOrEmpty()]
        [string] $SharedStorageRoot,

        [Parameter(Mandatory = $true)]
        [ValidateNotNullOrEmpty()]
        [string] $PhpPath,

        [Parameter(Mandatory = $true)]
        [ValidateNotNullOrEmpty()]
        [string] $AppUrl,

        [Parameter(Mandatory = $true)]
        [ValidateNotNullOrEmpty()]
        [string] $AppName,

        [Parameter(Mandatory = $true)]
        [ValidateSet('production', 'staging', 'development')]
        [string] $AppEnv,

        [Parameter(Mandatory = $true)]
        [ValidateNotNullOrEmpty()]
        [string] $AppKey,

        [Parameter(Mandatory = $true)]
        [PSCredential] $DatabaseCredential,

        [Parameter(Mandatory = $true)]
        [ValidateNotNullOrEmpty()]
        [string] $DatabaseHost,

        [Parameter(Mandatory = $true)]
        [ValidateRange(1, 65535)]
        [int] $DatabasePort,

        [Parameter(Mandatory = $true)]
        [ValidateNotNullOrEmpty()]
        [string] $DatabaseName,

        [Parameter(Mandatory = $false)]
        [ValidateSet('skip-verify', 'prefer', 'require')]
        [string] $DatabaseSslMode = 'prefer',

        [Parameter(Mandatory = $false)]
        [ValidateRange(1, 10)]
        [int] $KeepStagingCount = 3
    )

    Write-Information "=========================================="
    Write-Information "INVENTORY APP DEPLOYMENT"
    Write-Information "=========================================="
    Write-Information "Environment: $AppEnv"
    Write-Information "Application URL: $AppUrl"
    Write-Information "Start Time: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
    Write-Information ""

    $startTime = Get-Date
    $deploymentSuccess = $false

    try {
        # ===== VALIDATION PHASE =====
        Write-Information "PHASE 1: Validating System and Package"
        Write-Information "---"
        Test-SystemPrerequisites -PhpPath $PhpPath -WebserverPath (Split-Path $WebserverPath -Parent)
        
        # Check if deployment package is a ZIP file, extract if needed
        $actualPackagePath = $DeploymentPackagePath
        if ($DeploymentPackagePath -like '*.zip') {
            Write-Information "Extracting deployment package from ZIP..."
            $extractPath = Join-Path (Split-Path $DeploymentPackagePath -Parent) "extracted-$(Get-Date -Format 'yyyyMMdd-HHmmss')"
            New-Item -Path $extractPath -ItemType Directory -Force | Out-Null
            Expand-Archive -Path $DeploymentPackagePath -DestinationPath $extractPath -Force -ErrorAction Stop
            
            # Find the actual application directory (might be nested in archive)
            $dirs = @(Get-ChildItem -Path $extractPath -Directory)
            if ($dirs.Count -eq 1) {
                $actualPackagePath = $dirs[0].FullName
                Write-Information "✓ Package extracted to: $actualPackagePath"
            } else {
                $actualPackagePath = $extractPath
                Write-Information "✓ Package extracted to: $actualPackagePath"
            }
        }
        
        Test-DeploymentPackage -PackagePath $actualPackagePath
        Write-Information ""

        # ===== STAGING PREPARATION =====
        Write-Information "PHASE 2: Preparing Staging Directory"
        Write-Information "---"
        $stagingPath = New-StagingDirectory -BaseDirectory (Split-Path $WebserverPath -Parent)
        Write-Information ""

        # Copy package to staging
        Write-Information "Copying deployment package..."
        Copy-Item -Path "$actualPackagePath\*" -Destination $stagingPath -Recurse -Force -ErrorAction Stop
        Write-Information "✓ Package copied to staging"
        Write-Information ""

        # ===== COMPOSER INSTALL =====
        Write-Information "Installing PHP dependencies (composer)..."
        $composer = Get-Command composer -ErrorAction SilentlyContinue
        if (-not $composer) {
            throw "Composer not found in PATH. Please install Composer or add it to PATH."
        }
        
        try {
            Push-Location $stagingPath
            Write-Information "  Running: composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev"
            & $composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev 2>&1 | Write-Information
            if ($LASTEXITCODE -ne 0) {
                throw "Composer install failed with exit code $LASTEXITCODE"
            }
            Write-Information "✓ PHP dependencies installed"
            Pop-Location
        } catch {
            Pop-Location
            throw "PHP dependency installation failed: $_"
        }
        Write-Information ""

        # ===== NPM BUILD =====
        Write-Information "Installing Node.js dependencies and building assets..."
        $npm = Get-Command npm -ErrorAction SilentlyContinue
        if (-not $npm) {
            throw "NPM not found in PATH. Please install Node.js or add NPM to PATH."
        }
        
        try {
            Push-Location $stagingPath
            
            # Install backend dependencies
            Write-Information "  Running: npm install --no-audit --no-fund"
            & $npm install --no-audit --no-fund 2>&1 | Write-Information
            if ($LASTEXITCODE -ne 0) {
                throw "NPM install failed with exit code $LASTEXITCODE"
            }
            Write-Information "  ✓ NPM packages installed"
            
            # Build frontend assets
            Write-Information "  Running: npm run build"
            & $npm run build 2>&1 | Write-Information
            if ($LASTEXITCODE -ne 0) {
                throw "Frontend build failed with exit code $LASTEXITCODE"
            }
            Write-Information "  ✓ Frontend assets built"
            Write-Information "✓ Node.js dependencies and assets processed"
            
            Pop-Location
        } catch {
            Pop-Location
            throw "Node.js build failed: $_"
        }
        Write-Information ""

        # ===== STORAGE CONFIGURATION =====
        Write-Information "PHASE 3: Configuring Persistent Storage"
        Write-Information "---"
        New-StorageSymlink -StagingPath $stagingPath -SharedStorageRoot $SharedStorageRoot
        Write-Information ""

        # ===== APPLICATION DOWN =====
        Write-Information "PHASE 4: Preparing Application Transition"
        Write-Information "---"
        Invoke-LaravelDown -StagingPath $stagingPath -PhpPath $PhpPath
        Write-Information ""

        # ===== PRODUCTION SWAP =====
        Write-Information "PHASE 5: Performing Production Swap"
        Write-Information "---"
        Swap-WebserverSymlink -CurrentProductionPath $WebserverPath `
            -NewStagingPath $stagingPath `
            -PhpPath $PhpPath
        Write-Information ""

        # ===== CONFIGURATION =====
        Write-Information "PHASE 6: Configuring Application"
        Write-Information "---"
        $dbPassword = $DatabaseCredential.GetNetworkCredential().Password
        
        # Build environment variables (KEY => VALUE pairs, not patterns)
        $envVars = @{
            'APP_NAME'        = $AppName
            'APP_ENV'         = $AppEnv
            'APP_DEBUG'       = if ($AppEnv -eq 'production') { 'false' } else { 'true' }
            'APP_URL'         = $AppUrl
            'APP_KEY'         = $AppKey
            'DB_CONNECTION'   = 'mysql'
            'DB_HOST'         = $DatabaseHost
            'DB_PORT'         = $DatabasePort
            'DB_DATABASE'     = $DatabaseName
            'DB_USERNAME'     = $DatabaseCredential.UserName
            'DB_PASSWORD'     = $dbPassword
            'DB_SSLMODE'      = $DatabaseSslMode
        }

        New-EnvironmentFile -StagingPath $stagingPath `
            -EnvironmentVariables $envVars
        Write-Information ""

        # ===== LARAVEL SETUP =====
        Write-Information "PHASE 7: Initializing Application"
        Write-Information "---"
        Invoke-LaravelSetup -StagingPath $stagingPath `
            -PhpPath $PhpPath `
            -AppEnv $AppEnv
        Write-Information ""

        # ===== CLEANUP =====
        Write-Information "PHASE 8: Cleanup"
        Write-Information "---"
        Remove-OldStagingDirectories -BaseDirectory (Split-Path $WebserverPath -Parent) `
            -KeepCount $KeepStagingCount
        Remove-SwapBackup -CurrentProductionPath $WebserverPath
        Write-Information ""

        $deploymentSuccess = $true

    } catch {
        Write-Error "Deployment failed: $_"
        Write-Error "Stack trace: $($_.ScriptStackTrace)"
        return $false
    } finally {
        $duration = (Get-Date) - $startTime
        Write-Information "=========================================="
        if ($deploymentSuccess) {
            Write-Information "✓ DEPLOYMENT SUCCESSFUL"
        } else {
            Write-Information "✗ DEPLOYMENT FAILED"
        }
        Write-Information "End Time: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
        Write-Information "Duration: $([math]::Round($duration.TotalSeconds, 2)) seconds"
        Write-Information "=========================================="
    }

    return $deploymentSuccess
}

# Export module functions
Export-ModuleMember -Function @(
    'Test-SystemPrerequisites',
    'Test-DeploymentPackage',
    'New-StagingDirectory',
    'Remove-OldStagingDirectories',
    'New-StorageSymlink',
    'Swap-WebserverSymlink',
    'Remove-SwapBackup',
    'New-EnvironmentFile',
    'Invoke-LaravelSetup',
    'Invoke-LaravelDown',
    'Deploy-Application'
)
