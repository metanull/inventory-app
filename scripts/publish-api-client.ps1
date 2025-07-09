<#
.SYNOPSIS
    Publishes the generated TypeScript API client to npm.

.DESCRIPTION
    This script automates the process of publishing the generated API client to npm.
    It performs validation checks, builds the client, and publishes it.

.PARAMETER DryRun
    Performs a dry run without actually publishing to npm.

.PARAMETER Registry
    Specifies the npm registry to publish to. defaults to npmjs.com.

.EXAMPLE
    .\scripts\publish-api-client.ps1
    Publishes the client to npmjs.com.

.EXAMPLE
    .\scripts\publish-api-client.ps1 -DryRun
    Performs a dry run to check what would be published.

.NOTES
    Requires npm to be installed and configured with appropriate credentials.
    The API client must be generated first using generate-api-client.ps1.
#>

[CmdletBinding()]
param(
    [switch]$DryRun,
    [string]$Registry = "https://npm.pkg.github.com/",
    [ValidateNotNull()]
    [System.Management.Automation.PSCredential]
    [System.Management.Automation.Credential()]
    $Credential = [System.Management.Automation.PSCredential]::Empty
)

# Set strict mode for better error handling
Set-StrictMode -Version 3.0

# Ensure we're running from the project root
$ScriptPath = $PSScriptRoot
$ProjectRoot = Split-Path -Path $ScriptPath -Parent
Set-Location -Path $ProjectRoot

# Load configuration
$ConfigPath = Join-Path -Path $ScriptPath -ChildPath 'api-client-config.psd1'
if (-not (Test-Path -Path $ConfigPath)) {
    throw "Configuration file not found: $ConfigPath"
}

$Config = Import-PowerShellDataFile -Path $ConfigPath
$ClientPath = Join-Path -Path $ProjectRoot -ChildPath $Config.Paths.OutputDirectory
$PackageJsonPath = Join-Path -Path $ClientPath -ChildPath $Config.Paths.PackageJsonFile

# Validate that the client exists
if (-not (Test-Path -Path $ClientPath)) {
    Write-Error "API client not found at: $ClientPath"
    Write-Information "Run generate-api-client.ps1 first to generate the client."
    exit 1
}

if (-not (Test-Path -Path $PackageJsonPath)) {
    Write-Error "package.json not found at: $PackageJsonPath"
    exit 1
}

# Read package.json to get version info
$PackageInfo = Get-Content -Path $PackageJsonPath -Raw | ConvertFrom-Json
$PackageName = $PackageInfo.name
$PackageVersion = $PackageInfo.version

Write-Information "Publishing API client..."
Write-Information "Package: $PackageName"
Write-Information "Version: $PackageVersion"
Write-Information "Registry: $Registry"

# Change to client directory
Set-Location -Path $ClientPath

# Check if npm is logged in for the target registry
$whoami = & npm whoami --registry $Registry 2>$null
if (-not $whoami) {
    Write-Information "You are not logged in to $Registry. Running 'npm login'..."
    if ($Credential) {
        $npmUser = $Credential.UserName
        $npmPass = $Credential.GetNetworkCredential().Password
        $npmEmail = $CredentialEmail
        $loginInput = "${npmUser}`n${npmPass}`n${npmEmail}`n"
        $loginInput | & npm login --registry $Registry
    } else {
        & npm login --registry $Registry
    }
    # Re-check login
    $whoami = & npm whoami --registry $Registry 2>$null
    if (-not $whoami) {
        Write-Error "npm login failed. Please check your credentials and try again."
        exit 1
    }
}

# Prepare npm publish command
$PublishArgs = @("publish", "--access", "public", "--registry", $Registry)

# Handle version with build metadata by removing the build metadata part for npm
# AND adding a unique identifier to ensure no conflicts with previous versions
if ($PackageVersion -match '^(.+?)(?:\+(.+))?$') {
    $CleanVersion = $Matches[1]
    
    # If we found build metadata, generate a unique version using the timestamp from the build metadata
    if ($Matches.Count -gt 2 -and $Matches[2]) {
        $BuildInfo = $Matches[2]
        
        # Parse the date from build metadata (expected format: yyyyMMdd.HHmm)
        if ($BuildInfo -match '(\d{8})\.(\d{4})') {
            $DatePart = $Matches[1]
            $TimePart = $Matches[2]
            
            # Create a unique suffix based on the build timestamp (mmdd.HHMM format to keep it shorter)
            $UniqueSuffix = $DatePart.Substring(4, 4) + "." + $TimePart
            
            # If it's already a prerelease version (has a dash), add the unique suffix after it
            if ($CleanVersion -match '^(.+?)-(.+)$') {
                $VersionBase = $Matches[1]
                $PreReleaseTag = $Matches[2]
                $UniqueVersion = "$VersionBase-$PreReleaseTag.$UniqueSuffix"
            } else {
                # Otherwise add it as a prerelease suffix
                $UniqueVersion = "$CleanVersion-$UniqueSuffix"
            }
            
            # Update package.json with the unique version
            $PackageContent = Get-Content -Path $PackageJsonPath -Raw
            $PackageContent = $PackageContent -replace "`"version`": `"$([regex]::Escape($PackageVersion))`"", "`"version`": `"$UniqueVersion`""
            Set-Content -Path $PackageJsonPath -Value $PackageContent -Encoding UTF8
            Write-Information "Modified version for npm compatibility: $UniqueVersion (from $PackageVersion)"
            $PackageVersion = $UniqueVersion
        } else {
            # Fallback if we can't parse the build metadata
            $PackageContent = Get-Content -Path $PackageJsonPath -Raw
            $PackageContent = $PackageContent -replace "`"version`": `"$([regex]::Escape($PackageVersion))`"", "`"version`": `"$CleanVersion`""
            Set-Content -Path $PackageJsonPath -Value $PackageContent -Encoding UTF8
            Write-Information "Removed build metadata from version for npm compatibility: $CleanVersion"
            $PackageVersion = $CleanVersion
        }
    }
}

# Add --tag if version is prerelease
if ($PackageVersion -match '-') {
    $PublishArgs += @('--tag', 'dev')
    Write-Information "Detected prerelease version, adding --tag dev to npm publish."
}
if ($DryRun) {
    $PublishArgs += "--dry-run"
    Write-Information "Performing dry run..."
} else {
    Write-Information "Publishing to npm..."
}

# Execute npm publish
try {
    & npm @PublishArgs
    if ($LASTEXITCODE -eq 0) {
        if ($DryRun) {
            Write-Information "✔ Dry run completed successfully!"
            Write-Information "Package would be published as: $PackageName@$PackageVersion"
        } else {
            Write-Information "✔ Package published successfully!"
            Write-Information "Published: $PackageName@$PackageVersion"
            Write-Information "Install with: npm install $PackageName"
        }
    } else {
        Write-Error "npm publish failed with exit code: $LASTEXITCODE"
        exit 1
    }
} catch {
    Write-Error "Failed to publish package: $_"
    exit 1
} finally {
    # Return to project root
    Set-Location -Path $ProjectRoot
}

