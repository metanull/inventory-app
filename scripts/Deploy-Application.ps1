#Requires -Version 5.0
#Requires -RunAsAdministrator

<#
.SYNOPSIS
    Inventory App Deployment Script - Main entry point for application deployment.

.DESCRIPTION
    This script orchestrates the deployment of the Inventory Management API on Windows.
    It loads the InventoryApp.Deployment module and executes the deployment process
    with the provided parameters.

.PARAMETER DeploymentPackagePath
    Full path to extracted deployment package
    Example: C:\temp\inventory-app-release-20251031

.PARAMETER WebserverPath
    Full path to webserver directory where production symlink lives
    Example: C:\Apache24\htdocs\inventory-app

.PARAMETER SharedStorageRoot
    Root path for shared storage (parent of 'shared-storage' directory)
    Example: C:\mwnf-server\github-apps

.PARAMETER PhpPath
    Full path to PHP executable
    Example: C:\php\php.exe

.PARAMETER AppUrl
    Application URL (must include protocol)
    Example: https://inventory.museumwnf.org

.PARAMETER AppName
    Display name for the application
    Example: Inventory App

.PARAMETER AppEnv
    Application environment (production, staging, development)
    Default: production

.PARAMETER AppKey
    Laravel APP_KEY (base64 encoded, generated with 'php artisan key:generate')
    Example: base64:xxxxx...

.PARAMETER DatabaseHost
    Database server hostname or IP
    Example: 127.0.0.1

.PARAMETER DatabasePort
    Database server port
    Default: 3306

.PARAMETER DatabaseName
    Database name
    Example: inventory_db

.PARAMETER DatabaseUsername
    Database username
    Example: app

.PARAMETER DatabasePassword
    Database password (use -DatabasePassword or will prompt if not provided)

.PARAMETER DatabaseSslMode
    Database SSL mode: skip-verify, prefer, require
    Default: prefer

.PARAMETER GitHubToken
    GitHub Personal Access Token for npm authentication (optional)
    Only required if using private npm packages

.PARAMETER KeepStagingCount
    Number of staging directories to keep for rollback
    Default: 3

.EXAMPLE
    # Interactive deployment with credential prompt
    .\scripts\Deploy-Application.ps1 `
        -DeploymentPackagePath "C:\temp\inventory-app-release-20251031" `
        -WebserverPath "C:\Apache24\htdocs\inventory-app" `
        -SharedStorageRoot "C:\mwnf-server\github-apps" `
        -PhpPath "C:\php\php.exe" `
        -AppUrl "https://inventory.museumwnf.org" `
        -AppName "Inventory App" `
        -DatabaseHost "127.0.0.1" `
        -DatabaseName "inventory_db" `
        -DatabaseUsername "app" `
        -Verbose

.EXAMPLE
    # Non-interactive deployment (for CI/CD)
    $dbCred = New-Object System.Management.Automation.PSCredential (
        'app',
        (ConvertTo-SecureString 'secure-password' -AsPlainText -Force)
    )

    .\scripts\Deploy-Application.ps1 `
        -DeploymentPackagePath "C:\temp\inventory-app-release-20251031" `
        -WebserverPath "C:\Apache24\htdocs\inventory-app" `
        -SharedStorageRoot "C:\mwnf-server\github-apps" `
        -PhpPath "C:\php\php.exe" `
        -AppUrl "https://inventory.museumwnf.org" `
        -AppName "Inventory App" `
        -AppEnv "production" `
        -AppKey "base64:xxxxx..." `
        -DatabaseHost "127.0.0.1" `
        -DatabasePort 3306 `
        -DatabaseName "inventory_db" `
        -DatabaseCredential $dbCred `
        -Verbose

.NOTES
    Author: Museum With No Frontiers
    Version: 1.0.0
    Requires: PowerShell 5.0+, Administrator privileges, PHP 8.2+
    Exit Codes:
        0 - Successful deployment
        1 - Deployment error
        2 - Prerequisites failed

.LINK
    https://github.com/metanull/inventory-app
#>

[CmdletBinding()]
param(
    [Parameter(Mandatory = $true, HelpMessage = "Path to extracted deployment package")]
    [ValidateNotNullOrEmpty()]
    [string] $DeploymentPackagePath,

    [Parameter(Mandatory = $true, HelpMessage = "Path to webserver deployment directory")]
    [ValidateNotNullOrEmpty()]
    [string] $WebserverPath,

    [Parameter(Mandatory = $true, HelpMessage = "Root path for shared storage")]
    [ValidateNotNullOrEmpty()]
    [string] $SharedStorageRoot,

    [Parameter(Mandatory = $true, HelpMessage = "Path to PHP executable")]
    [ValidateNotNullOrEmpty()]
    [string] $PhpPath,

    [Parameter(Mandatory = $true, HelpMessage = "Application URL")]
    [ValidateNotNullOrEmpty()]
    [string] $AppUrl,

    [Parameter(Mandatory = $true, HelpMessage = "Application name")]
    [ValidateNotNullOrEmpty()]
    [string] $AppName,

    [Parameter(Mandatory = $false, HelpMessage = "Application environment")]
    [ValidateSet('production', 'staging', 'development')]
    [string] $AppEnv = 'production',

    [Parameter(Mandatory = $false, HelpMessage = "Laravel APP_KEY (base64 encoded)")]
    [ValidateNotNullOrEmpty()]
    [string] $AppKey,

    [Parameter(Mandatory = $true, HelpMessage = "Database hostname")]
    [ValidateNotNullOrEmpty()]
    [string] $DatabaseHost,

    [Parameter(Mandatory = $false, HelpMessage = "Database port")]
    [ValidateRange(1, 65535)]
    [int] $DatabasePort = 3306,

    [Parameter(Mandatory = $true, HelpMessage = "Database name")]
    [ValidateNotNullOrEmpty()]
    [string] $DatabaseName,

    [Parameter(Mandatory = $true, HelpMessage = "Database username")]
    [ValidateNotNullOrEmpty()]
    [string] $DatabaseUsername,

    [Parameter(Mandatory = $false, HelpMessage = "Database password as SecureString")]
    [SecureString] $DatabasePassword,

    [Parameter(Mandatory = $false, HelpMessage = "Database SSL mode")]
    [ValidateSet('skip-verify', 'prefer', 'require')]
    [string] $DatabaseSslMode = 'prefer',

    [Parameter(Mandatory = $false, HelpMessage = "GitHub Personal Access Token")]
    [string] $GitHubToken,

    [Parameter(Mandatory = $false, HelpMessage = "Number of staging directories to keep")]
    [ValidateRange(1, 10)]
    [int] $KeepStagingCount = 3
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

# ============================================================================
# INITIALIZATION
# ============================================================================

Write-Information "Inventory App Deployment Script v1.0.0" -InformationAction Continue
Write-Information "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" -InformationAction Continue
Write-Information "" -InformationAction Continue

# Get script directory
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$modulePath = Join-Path $scriptDir 'InventoryApp.Deployment'

# Verify module exists
if (-not (Test-Path $modulePath)) {
    Write-Error "Deployment module not found at: $modulePath"
    exit 2
}

# Import deployment module
try {
    Write-Information "Loading deployment module..." -InformationAction Continue
    Import-Module $modulePath -Force -ErrorAction Stop
    Write-Information "✓ Module loaded" -InformationAction Continue
} catch {
    Write-Error "Failed to load deployment module: $_"
    exit 2
}

# ============================================================================
# DATABASE CREDENTIAL HANDLING
# ============================================================================

$databaseCredential = $null

if ($PSBoundParameters.ContainsKey('DatabasePassword')) {
    # Use provided SecureString password
    $databaseCredential = New-Object System.Management.Automation.PSCredential ($DatabaseUsername, $DatabasePassword)
} else {
    # Prompt for password
    Write-Information "Database credentials required..." -InformationAction Continue
    $databaseCredential = Get-Credential -UserName $DatabaseUsername -Message "Enter database password for user '$DatabaseUsername'"

    if (-not $databaseCredential) {
        Write-Error "Database credentials not provided"
        exit 1
    }
}

# ============================================================================
# APP KEY HANDLING
# ============================================================================

if (-not $AppKey) {
    Write-Error "APP_KEY is required. Generate with: php artisan key:generate"
    exit 1
}

# ============================================================================
# EXECUTION
# ============================================================================

try {
    Write-Information "" -InformationAction Continue

    # Call deployment function
    $deploymentParams = @{
        DeploymentPackagePath = $DeploymentPackagePath
        WebserverPath         = $WebserverPath
        SharedStorageRoot     = $SharedStorageRoot
        PhpPath               = $PhpPath
        AppUrl                = $AppUrl
        AppName               = $AppName
        AppEnv                = $AppEnv
        AppKey                = $AppKey
        DatabaseCredential    = $databaseCredential
        DatabaseHost          = $DatabaseHost
        DatabasePort          = $DatabasePort
        DatabaseName          = $DatabaseName
        DatabaseSslMode       = $DatabaseSslMode
        KeepStagingCount      = $KeepStagingCount
        Verbose               = $VerbosePreference -ne 'SilentlyContinue'
    }

    $success = Deploy-Application @deploymentParams

    if ($success) {
        Write-Information "" -InformationAction Continue
        Write-Information "Deployment completed successfully!" -InformationAction Continue
        exit 0
    } else {
        Write-Error "Deployment completed with errors"
        exit 1
    }

} catch {
    Write-Error "Deployment script failed: $_"
    Write-Error "Stack trace: $($_.ScriptStackTrace)"
    exit 1
} finally {
    # Cleanup
    if ($null -ne $databaseCredential) {
        $databaseCredential.Dispose()
    }
}
