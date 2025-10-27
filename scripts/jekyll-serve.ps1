<#
.SYNOPSIS
    Serve the Jekyll documentation site locally.

.DESCRIPTION
    This script starts a local Jekyll server for the documentation site by running Jekyll in WSL.
    It automatically detects the Ruby installation path (user-installed preferred).

.PARAMETER Host
    The host address to bind to. Default is "127.0.0.1".

.PARAMETER Port
    The port to serve on. Default is 4000.

.PARAMETER BaseUrl
    The base URL for the Jekyll site. Default is "" (empty for local development).

.PARAMETER LiveReload
    Enable LiveReload to automatically refresh the browser when files change.

.EXAMPLE
    .\scripts\jekyll-serve.ps1
    Serve the site with default settings

.EXAMPLE
    .\scripts\jekyll-serve.ps1 -Port 8080 -LiveReload
    Serve on port 8080 with LiveReload enabled

.EXAMPLE
    .\scripts\jekyll-serve.ps1 -Host 0.0.0.0
    Serve on all network interfaces
#>

[CmdletBinding()]
param(
    [Parameter(Mandatory=$false)]
    [string]$HostAddress = "127.0.0.1",
    
    [Parameter(Mandatory=$false)]
    [int]$Port = 4000,
    
    [Parameter(Mandatory=$false)]
    [string]$BaseUrl = "",
    
    [Parameter(Mandatory=$false)]
    [switch]$LiveReload
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
Write-Host "🌐 Starting Jekyll server..." -ForegroundColor Cyan
Write-Host "  Ruby path: $foundRubyPath" -ForegroundColor Gray
Write-Host "  Host: $HostAddress" -ForegroundColor Gray
Write-Host "  Port: $Port" -ForegroundColor Gray

# Build the command
$serveArgs = @(
    "--host `"$HostAddress`"",
    "--port $Port"
)

if ($BaseUrl) {
    $serveArgs += "--baseurl `"$BaseUrl`""
    Write-Host "  Base URL: $BaseUrl" -ForegroundColor Gray
}

if ($LiveReload) {
    $serveArgs += "--livereload"
    Write-Host "  LiveReload: Enabled" -ForegroundColor Gray
}

$serveArgsString = $serveArgs -join " "

# Run Jekyll serve
$pathVar = "`$PATH"
$pathExport = "export PATH=`"$foundRubyPath`:$pathVar`""
$serveCmd = "wsl bash -lc '$pathExport && cd docs && bundle exec jekyll serve $serveArgsString'"

Write-Host ""
Write-Host "Running: bundle exec jekyll serve $serveArgsString" -ForegroundColor Gray
Write-Host ""
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray
Write-Host ""
Write-Host "  📚 Server will be available at:" -ForegroundColor Green
Write-Host "     http://$HostAddress`:$Port" -ForegroundColor Cyan
Write-Host ""
Write-Host "  Press Ctrl+C to stop the server" -ForegroundColor Yellow
Write-Host ""
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray
Write-Host ""

try {
    Invoke-Expression $serveCmd
    $exitCode = $LASTEXITCODE
    
    if ($null -ne $exitCode -and $exitCode -ne 0) {
        Write-Host ""
        Write-Host "❌ Jekyll server stopped with exit code: $exitCode" -ForegroundColor Red
        exit $exitCode
    }
} catch {
    Write-Host ""
    Write-Host "❌ Error running Jekyll server: $_" -ForegroundColor Red
    exit 1
}
