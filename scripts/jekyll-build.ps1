<#
.SYNOPSIS
    Build the Jekyll documentation site.

.DESCRIPTION
    This script builds the Jekyll documentation site by running Jekyll in WSL.
    It automatically detects the Ruby installation path (user-installed preferred).

.PARAMETER BaseUrl
    The base URL for the Jekyll site. Default is "/inventory-app".

.PARAMETER Clean
    Clean the site before building.

.EXAMPLE
    .\scripts\jekyll-build.ps1
    Build the site with default settings

.EXAMPLE
    .\scripts\jekyll-build.ps1 -BaseUrl "/my-app" -Clean
    Build the site with custom base URL and clean first
#>

[CmdletBinding()]
param(
    [Parameter(Mandatory=$false)]
    [string]$BaseUrl = "/inventory-app",
    
    [Parameter(Mandatory=$false)]
    [switch]$Clean
)

# Enable strict mode
Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

Write-Host "🔍 Detecting Ruby installation in WSL..." -ForegroundColor Cyan

# Function to test if a Ruby path exists in WSL
function Test-WslRubyPath {
    param([string]$Path)
    
    $testCmd = "wsl bash -c 'test -d ""$Path"" && echo EXISTS || echo MISSING'"
    $result = Invoke-Expression $testCmd
    return $result -eq "EXISTS"
}

# Try to find Ruby in common locations (user install first, then system)
$rubyPaths = @(
    "`$HOME/.local/share/gem/ruby/3.2.0/bin",  # User-installed Ruby 3.2.0
    "`$HOME/.local/share/gem/ruby/3.3.0/bin",  # User-installed Ruby 3.3.0
    "`$HOME/.rbenv/shims",                      # rbenv installation
    "`$HOME/.rvm/bin",                          # RVM installation
    "/usr/local/lib/ruby/gems/3.2.0/bin",      # System-wide Ruby 3.2.0
    "/usr/local/lib/ruby/gems/3.3.0/bin",      # System-wide Ruby 3.3.0
    "/var/lib/gems/3.2.0/bin",                 # Debian/Ubuntu system Ruby 3.2.0
    "/var/lib/gems/3.3.0/bin"                  # Debian/Ubuntu system Ruby 3.3.0
)

$foundRubyPath = $null
foreach ($path in $rubyPaths) {
    Write-Host "  Checking: $path" -ForegroundColor Gray
    if (Test-WslRubyPath -Path $path) {
        $foundRubyPath = $path
        Write-Host "  ✓ Found Ruby at: $path" -ForegroundColor Green
        break
    }
}

if (-not $foundRubyPath) {
    Write-Host "❌ Error: Could not find Ruby installation in WSL" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please install Ruby in WSL using one of these methods:" -ForegroundColor Yellow
    Write-Host "  1. User installation (recommended):"
    Write-Host "     sudo apt update && sudo apt install ruby-full"
    Write-Host "     gem install bundler jekyll --user-install"
    Write-Host ""
    Write-Host "  2. rbenv:"
    Write-Host "     curl -fsSL https://github.com/rbenv/rbenv-installer/raw/HEAD/bin/rbenv-installer | bash"
    Write-Host "     rbenv install 3.2.3"
    Write-Host ""
    exit 1
}

Write-Host ""
Write-Host "🏗️  Building Jekyll site..." -ForegroundColor Cyan
Write-Host "  Ruby path: $foundRubyPath" -ForegroundColor Gray
Write-Host "  Base URL: $BaseUrl" -ForegroundColor Gray

# Build the command
$buildArgs = @()
if ($Clean) {
    $buildArgs += "--clean"
    Write-Host "  Clean build: Yes" -ForegroundColor Gray
}
$buildArgs += "--baseurl ""$BaseUrl"""

$buildArgsString = $buildArgs -join " "

# Run Jekyll build
$pathVar = "`$PATH"
$pathExport = "export PATH=`"$foundRubyPath`:$pathVar`""
$buildCmd = "wsl bash -lc '$pathExport && cd docs && bundle exec jekyll build $buildArgsString'"

Write-Host ""
Write-Host "Running: bundle exec jekyll build $buildArgsString" -ForegroundColor Gray
Write-Host ""

try {
    Invoke-Expression $buildCmd
    $exitCode = $LASTEXITCODE
    
    if ($exitCode -eq 0) {
        Write-Host ""
        Write-Host "✅ Jekyll build completed successfully!" -ForegroundColor Green
        Write-Host "   Output: docs/_site/" -ForegroundColor Gray
    } else {
        Write-Host ""
        Write-Host "❌ Jekyll build failed with exit code: $exitCode" -ForegroundColor Red
        exit $exitCode
    }
} catch {
    Write-Host ""
    Write-Host "❌ Error running Jekyll build: $_" -ForegroundColor Red
    exit 1
}
