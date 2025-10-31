#!/usr/bin/env pwsh
<#
.SYNOPSIS
Simulates the GitHub continuous deployment build pipeline locally.

.DESCRIPTION
Clones the repository to a temp directory, checks out a branch, and runs the full
build pipeline to validate that the CI/CD workflow will succeed before pushing.

Parameters are auto-detected from the current working directory if not provided.

.PARAMETER RepositoryUrl
The Git repository URL (e.g., https://github.com/metanull/inventory-app.git)
Auto-detected from git remote origin if not provided.

.PARAMETER BranchName
The branch name to checkout (e.g., main, feature/xyz)
Auto-detected from current branch if not provided.

.PARAMETER NpmrcPath
Path to .npmrc file for GitHub Package authentication.
Auto-detected from project root first, then $HOME\.npmrc if not provided.
Can be explicitly set to override auto-detection.

.EXAMPLE
# Run with all parameters auto-detected
./Invoke-LocalCDBuild.ps1

.EXAMPLE
# Specify only repository URL (branch and npmrc auto-detected)
./Invoke-LocalCDBuild.ps1 -RepositoryUrl "https://github.com/metanull/inventory-app.git"

.EXAMPLE
# Specify all parameters explicitly
./Invoke-LocalCDBuild.ps1 -RepositoryUrl "https://github.com/metanull/inventory-app.git" -BranchName "main" -NpmrcPath "$HOME\.npmrc"
#>

param(
    [Parameter(Mandatory = $false)]
    [string]$RepositoryUrl,
    
    [Parameter(Mandatory = $false)]
    [string]$BranchName,
    
    [Parameter(Mandatory = $false)]
    [string]$NpmrcPath
)

$ErrorActionPreference = 'Stop'

# Auto-detect parameters if empty
if (-not $RepositoryUrl) {
    $RepositoryUrl = git config --get remote.origin.url
    if (-not $RepositoryUrl) {
        Write-Error "ERROR: Could not auto-detect repository URL. Please provide -RepositoryUrl parameter." -ErrorAction Stop
    }
}

if (-not $BranchName) {
    $BranchName = git rev-parse --abbrev-ref HEAD
    if (-not $BranchName) {
        Write-Error "ERROR: Could not auto-detect branch name. Please provide -BranchName parameter." -ErrorAction Stop
    }
}

if (-not $NpmrcPath) {
    # Check for .npmrc in project root first, then in $HOME
    if (Test-Path ".npmrc") {
        $NpmrcPath = (Resolve-Path ".npmrc").Path
    } else {
        Write-Error "ERROR: Could not find .npmrc file in project root or $HOME. Please provide -NpmrcPath parameter." -ErrorAction Stop
    }
}

$tempDir = Join-Path ([System.IO.Path]::GetTempPath()) "cd-build-$(Get-Random)"

Write-Verbose "Starting local CD build simulation"
Write-Verbose "Repository: $RepositoryUrl"
Write-Verbose "Branch: $BranchName"
Write-Verbose "Temp directory: $tempDir"

try {
    # Check for uncommitted changes
    Write-Verbose "Checking for uncommitted or staged changes..."
    $gitStatus = git status --porcelain
    if ($gitStatus) {
        $gitStatus | ForEach-Object { 
            Write-Verbose ($_)
        }
        Write-Error "ERROR: You have uncommitted or staged changes. Please commit or stash them first." -ErrorAction Stop
    }

    # Verify .npmrc exists
    if (-not (Test-Path $NpmrcPath)) {
        Write-Error "ERROR: .npmrc file not found at $NpmrcPath" -ErrorAction Stop
    }

    # Create temp directory
    New-Item -ItemType Directory -Path $tempDir -ErrorAction Stop | Out-Null

    # Clone repository
    Write-Verbose "Cloning repository into $($tempDir)..."
    git clone --quiet $RepositoryUrl $tempDir
    if ($LASTEXITCODE -ne 0) {
        Write-Error "ERROR: Failed to clone repository" -ErrorAction Stop
    }

    # Checkout branch
    Write-Verbose "Checking out branch: $BranchName"
    Push-Location $tempDir
    git checkout --quiet $BranchName
    if ($LASTEXITCODE -ne 0) {
        Write-Error "ERROR: Failed to checkout branch $BranchName" -ErrorAction Stop
    }

    # Copy .npmrc
    Write-Verbose "Copying .npmrc..."
    Copy-Item $NpmrcPath -Destination (Join-Path $tempDir ".npmrc") -Force
    
    # Composer install
    Write-Verbose "Running: composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev"
    & composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
    if ($LASTEXITCODE -ne 0) {
        Write-Error "ERROR: Composer install failed" -ErrorAction Stop
    }

    # Pint tests (root)
    Write-Verbose "Running: .\vendor\bin\pint (backend)"
    & .\vendor\bin\pint --test
    if ($LASTEXITCODE -ne 0) {
        Write-Error "ERROR: Pint tests failed (backend)" -ErrorAction Stop
    }

    # NPM install (root)
    Write-Verbose "Running: npm install --no-audit --no-fund (backend)"
    & npm install --no-audit --no-fund
    if ($LASTEXITCODE -ne 0) {
        Write-Error "ERROR: install failed (backend)" -ErrorAction Stop
    }

    # NPM install (SPA)
    Write-Verbose "Running: npm install --no-audit --no-fund (SPA)"
    try {
        Push-Location ./spa
        & npm install --no-audit --no-fund
        if ($LASTEXITCODE -ne 0) {
            Write-Error "ERROR: install failed (SPA)" -ErrorAction Stop
        }
    } finally {
        Pop-Location
    }

    # eslint check (root)
    Write-Verbose "Running: eslint . (backend)"
    & eslint .
    if ($LASTEXITCODE -ne 0) {
        Write-Error "ERROR: lint check failed (backend)" -ErrorAction Stop
    }

    # eslint check (SPA)
    Write-Verbose "Running: eslint . (SPA)"
    try {
        Push-Location ./spa
        & eslint .
        if ($LASTEXITCODE -ne 0) {
            Write-Error "ERROR: lint check failed (SPA)" -ErrorAction Stop
        }
    } finally {
        Pop-Location
    }

    # TypeScript check (SPA)
    Write-Verbose "Running: vue-tsc --noEmit (SPA)"
    try {
        Push-Location ./spa
        & vue-tsc --noEmit
        if ($LASTEXITCODE -ne 0) {
            Write-Error "ERROR: TypeScript check failed (SPA)" -ErrorAction Stop
        }
    } finally {
        Pop-Location
    }

    # Build backend
    Write-Verbose "Running: npm run build (backend)"
    & npm run build
    if ($LASTEXITCODE -ne 0) {
        Write-Error "ERROR: build failed (backend)" -ErrorAction Stop
    }

    # Build SPA
    Write-Verbose "Running: npm run build (SPA)"
    try {
        Push-Location ./spa
        & npm run build
        if ($LASTEXITCODE -ne 0) {
            Write-Error "ERROR: build failed (SPA)" -ErrorAction Stop
        }
    } finally {
        Pop-Location
    }
    Write-Verbose "SUCCESS: All build steps completed successfully"
}
catch {
    Write-Error "ERROR: $($_.Exception.Message)" -ErrorAction Stop
}
finally {
    Pop-Location
    
    # Cleanup
    if (Test-Path $tempDir) {
        Write-Verbose "Cleaning up temp directory..."
        Remove-Item $tempDir -Recurse -Confirm -ErrorAction SilentlyContinue
    }
}
