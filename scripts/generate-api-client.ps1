<#
.SYNOPSIS
    Generates a TypeScript Axios client from an OpenAPI specification.

.DESCRIPTION
    This script generates a TypeScript client using openapi-generator-cli and creates
    accompanying package.json and README.md files. The script ensures it runs from
    the project root directory and uses configurable paths and templates.

.PARAMETER Force
    Forces regeneration of package.json and README.md files even if they already exist.

.PARAMETER Version
    Explicitly sets the version number for the generated client package.
    If not specified, version will be determined by the configured strategy.

.PARAMETER NoVersionIncrement
    Prevents automatic version incrementing and uses the base version from config.

.PARAMETER IncrementType
    Specifies which part of the version to increment: 'major', 'minor', or 'patch'.
    If not specified, defaults to 'patch' or the value in the config file.

.EXAMPLE
    .\scripts\generate-api-client.ps1
    Generates the API client with default settings.

.EXAMPLE
    .\scripts\generate-api-client.ps1 -Force
    Generates the API client and overwrites existing package.json and README.md files.

.EXAMPLE
    .\scripts\generate-api-client.ps1 -IncrementType minor
    Generates the API client and increments the minor version number.

.NOTES
    Requires openapi-generator-cli to be available via npx.
    Configuration is loaded from scripts\api-client-config.psd1.
#>

[CmdletBinding()]
param(
    [switch]$Force,
    [string]$Version,
    [switch]$NoVersionIncrement,
    [ValidateSet('major', 'minor', 'patch')]
    [string]$IncrementType = 'patch'
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

# Function to calculate version based on strategy
function Get-ClientVersion {
    param(
        [hashtable]$Config,
        [string]$ExplicitVersion,
        [bool]$NoIncrement,
        [string]$OpenApiSpecPath,
        [string]$OverrideIncrementType
    )

    if ($ExplicitVersion) {
        return $ExplicitVersion
    }

    $BaseVersion = $Config.PackageConfig.Version
    $VersionConfig = $Config.Versioning

    if ($NoIncrement -or $VersionConfig.Strategy -eq 'manual') {
        return $BaseVersion
    }

    switch ($VersionConfig.Strategy) {
        'auto' {
            # Try to read existing version from package.json and increment
            $ExistingPackageJson = Join-Path -Path $OutputPath -ChildPath $Config.Paths.PackageJsonFile
            if (Test-Path -Path $ExistingPackageJson) {
                try {
                    $ExistingPackage = Get-Content -Path $ExistingPackageJson -Raw | ConvertFrom-Json
                    $ExistingVersion = $ExistingPackage.version

                    # Parse version (assuming semver format)
                    # Match the version with optional prerelease and build metadata
                    if ($ExistingVersion -match '^(\d+)\.(\d+)\.(\d+)(?:-([^+]+))?(?:\+(.+))?') {
                        $Major = [int]$Matches[1]
                        $Minor = [int]$Matches[2]
                        $Patch = [int]$Matches[3]
                        # Capture prerelease part if present
                        $PreRelease = if ($Matches.Count -gt 4 -and $Matches[4]) { $Matches[4] } else { $null }
                        if($PreRelease) {
                            Write-Warning "Existing version has pre-release identifier: $PreRelease. Incrementing will not change it."
                        }

                        # Always increment according to the strategy
                        # This follows semantic versioning conventions
                        # Use the override increment type if provided, otherwise use the config value
                        $EffectiveIncrementType = if ($OverrideIncrementType) { $OverrideIncrementType } else { $VersionConfig.IncrementType }
                        switch ($EffectiveIncrementType) {
                            'major' { $Major++; $Minor = 0; $Patch = 0 }
                            'minor' { $Minor++; $Patch = 0 }
                            'patch' { $Patch++ }
                        }

                        $NewVersion = "$Major.$Minor.$Patch"
                    } else {
                        $NewVersion = $BaseVersion
                    }
                } catch {
                    $NewVersion = $BaseVersion
                }
            } else {
                $NewVersion = $BaseVersion
            }
        }

        'timestamp' {
            $Timestamp = Get-Date -Format "yyyyMMdd.HHmm"
            $NewVersion = "$BaseVersion-$Timestamp"
        }

        'hash' {
            if (Test-Path -Path $OpenApiSpecPath) {
                $Hash = Get-FileHash -Path $OpenApiSpecPath -Algorithm SHA256
                $ShortHash = $Hash.Hash.Substring(0, 8)
                $NewVersion = "$BaseVersion-$ShortHash"
            } else {
                $NewVersion = $BaseVersion
            }
        }

        default {
            $NewVersion = $BaseVersion
        }
    }

    # Add pre-release identifier if configured
    if ($VersionConfig.PreReleaseIdentifier -and -not $ExplicitVersion) {
        # Check if it already has a prerelease identifier
        if (-not ($NewVersion -match '-')) {
            $NewVersion = "$NewVersion-$($VersionConfig.PreReleaseIdentifier)"
        }
    }

    # Add build metadata if configured
    if ($VersionConfig.IncludeBuildMetadata -and -not $ExplicitVersion) {
        # First remove any existing build metadata
        if ($NewVersion -match '^(.+?)(?:\+.+)?$') {
            $NewVersion = $Matches[1]
        }
        $BuildMetadata = Get-Date -Format "yyyyMMdd.HHmm"
        $NewVersion = "$NewVersion+$BuildMetadata"
    }

    return $NewVersion
}

# Define paths (using appropriate separators for different contexts)
$OpenApiSpecPath = $Config.Paths.OpenApiSpec -replace '\\', '/'  # Use forward slashes for npx command
$OutputDirectory = $Config.Paths.OutputDirectory
$OutputPath = Join-Path -Path $ProjectRoot -ChildPath $OutputDirectory
$PackageJsonPath = Join-Path -Path $OutputPath -ChildPath $Config.Paths.PackageJsonFile
$ReadmePath = Join-Path -Path $OutputPath -ChildPath $Config.Paths.ReadmeFile

# Calculate the version to use
$ClientVersion = Get-ClientVersion -Config $Config -ExplicitVersion $Version -NoIncrement $NoVersionIncrement -OpenApiSpecPath (Join-Path -Path $ProjectRoot -ChildPath $Config.Paths.OpenApiSpec) -OverrideIncrementType $IncrementType
Write-Information "Using version: $ClientVersion"

# Generate the TypeScript client
Write-Information "Generating TypeScript client..."
try {
    & npx openapi-generator-cli generate -i $OpenApiSpecPath -g $Config.Generator.Type -o $OutputDirectory
    if ($LASTEXITCODE -eq 0) {
        Write-Information "✔ Client generated in $OutputDirectory"
    } else {
        throw "openapi-generator-cli failed with exit code: $LASTEXITCODE"
    }
} catch {
    Write-Error "Failed to generate client: $_"
    exit 1
}

# Create package.json if it doesn't exist or Force is specified
if (-not (Test-Path -Path $PackageJsonPath) -or $Force) {
    Write-Information "Creating package.json..."
    $PackageContent = $Config.Templates.PackageJson -f @(
        $Config.PackageConfig.Name,
        $ClientVersion,
        $Config.PackageConfig.Main,
        $Config.PackageConfig.Types,
        $Config.PackageConfig.Description,
        $Config.PackageConfig.Repository,
        $Config.PackageConfig.Author,
        $Config.PackageConfig.License
    )
    Set-Content -Path $PackageJsonPath -Value $PackageContent -Encoding UTF8
    Write-Information "✔ package.json created"
}

# Create README.md if it doesn't exist or Force is specified
if (-not (Test-Path -Path $ReadmePath) -or $Force) {
    Write-Information "Creating README.md..."
    $ReadmeContent = $Config.Templates.ReadmeContent -f @(
        $Config.PackageConfig.Name,
        $Config.PackageConfig.Name,
        $Config.PackageConfig.Name,
        $OutputDirectory
    )
    Set-Content -Path $ReadmePath -Value $ReadmeContent -Encoding UTF8
    Write-Information "✔ README.md created"
}

Write-Information "`n✔ API client generation completed successfully!"
Write-Information "Output directory: $OutputPath"
Write-Information "Generated version: $ClientVersion"
Write-Information "Edit the files as needed before publishing."

