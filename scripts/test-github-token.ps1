# Test GitHub Token Validity and Permissions
# This script verifies a GitHub token and displays its scopes/permissions

[CmdletBinding(DefaultParameterSetName='Credential')]
param(
    [Parameter(Mandatory=$true, ParameterSetName='Credential', HelpMessage="GitHub Personal Access Token")]
    [System.Management.Automation.PSCredential]
    [System.Management.Automation.Credential()]
    $Credential = [System.Management.Automation.PSCredential]::Empty,

    [Parameter(Mandatory=$true, ParameterSetName='DotSecret', HelpMessage="Read token from .secrets file")]
    [switch]$DotSecret
)

# Colors for output
function Write-Success { Write-Host $args -ForegroundColor Green }
function Write-Info { Write-Host $args -ForegroundColor Cyan }
function Write-Warning { Write-Host $args -ForegroundColor Yellow }
function Write-Error { Write-Host $args -ForegroundColor Red }

Write-Info "=========================================="
Write-Info "GitHub Token Verification"
Write-Info "=========================================="

# Get token based on parameter set
$Token = $null

if ($PSCmdlet.ParameterSetName -eq 'DotSecret') {
    Write-Info "`nReading token from .secrets file..."
    if (Test-Path ".secrets") {
        $secretsContent = Get-Content ".secrets"
        $tokenLine = $secretsContent | Where-Object { $_ -match "^GITHUB_TOKEN=(.+)$" }
        if ($tokenLine) {
            $Token = $matches[1]
            Write-Success "✅ Token found in .secrets file"
        } else {
            Write-Error "❌ GITHUB_TOKEN not found in .secrets file"
            exit 1
        }
    } else {
        Write-Error "❌ .secrets file not found"
        exit 1
    }
} else {
    # Get token from credential parameter
    if ($Credential -ne [System.Management.Automation.PSCredential]::Empty) {
        $Token = $Credential.GetNetworkCredential().Password
        Write-Success "✅ Token provided via credential parameter"
    } else {
        Write-Error "❌ No token provided"
        Write-Info "Usage:"
        Write-Info "  .\scripts\test-github-token.ps1 -Credential (Get-Credential -Message 'Enter GitHub token as password')"
        Write-Info "  .\scripts\test-github-token.ps1 -DotSecret"
        exit 1
    }
}

if ([string]::IsNullOrWhiteSpace($Token)) {
    Write-Error "❌ Token is empty"
    exit 1
}

# Mask the token in output
$maskedToken = $Token.Substring(0, 7) + "..." + $Token.Substring($Token.Length - 4)
Write-Info "`nTesting token: $maskedToken"

# Test 1: Basic token validation
Write-Info "`n📋 Test 1: Verifying token validity..."

try {
    $headers = @{
        "Authorization" = "Bearer $Token"
        "Accept" = "application/vnd.github+json"
        "X-GitHub-Api-Version" = "2022-11-28"
    }
    
    $response = Invoke-WebRequest -Uri "https://api.github.com/user" -Headers $headers -Method Get
    
    if ($response.StatusCode -eq 200) {
        Write-Success "✅ Token is VALID"
        
        # Parse user information
        $user = $response.Content | ConvertFrom-Json
        Write-Info "`nAuthenticated as:"
        Write-Host "  Username: $($user.login)" -ForegroundColor White
        Write-Host "  Name: $($user.name)" -ForegroundColor White
        Write-Host "  Email: $($user.email)" -ForegroundColor White
        Write-Host "  Type: $($user.type)" -ForegroundColor White
    } else {
        Write-Error "❌ Token validation failed with status: $($response.StatusCode)"
        exit 1
    }
} catch {
    Write-Error "❌ Token is INVALID or has no permissions"
    Write-Error "Error: $($_.Exception.Message)"
    if ($_.Exception.Response) {
        $statusCode = $_.Exception.Response.StatusCode.value__
        Write-Error "HTTP Status: $statusCode"
        
        if ($statusCode -eq 401) {
            Write-Warning "`n⚠️ 401 Unauthorized - Token is invalid or expired"
        } elseif ($statusCode -eq 403) {
            Write-Warning "`n⚠️ 403 Forbidden - Token may be valid but has insufficient permissions"
        }
    }
    exit 1
}

# Test 2: Check token scopes/permissions
Write-Info "`n📋 Test 2: Checking token scopes..."

$scopes = $response.Headers['X-OAuth-Scopes']
if ($scopes) {
    $scopeList = $scopes -split ', '
    Write-Success "✅ Token has the following scopes:"
    foreach ($scope in $scopeList) {
        Write-Host "  • $scope" -ForegroundColor White
    }
} else {
    Write-Warning "⚠️ No scopes header found (might be a fine-grained token)"
}

# Check rate limit info
$rateLimit = $response.Headers['X-RateLimit-Limit']
$rateLimitRemaining = $response.Headers['X-RateLimit-Remaining']
$rateLimitReset = $response.Headers['X-RateLimit-Reset']

if ($rateLimit) {
    Write-Info "`nRate Limit Information:"
    Write-Host "  Limit: $rateLimit requests/hour" -ForegroundColor White
    Write-Host "  Remaining: $rateLimitRemaining" -ForegroundColor White
    if ($rateLimitReset) {
        # Handle both single value and array (PowerShell sometimes returns arrays for headers)
        $resetValue = if ($rateLimitReset -is [array]) { $rateLimitReset[0] } else { $rateLimitReset }
        $resetTime = [DateTimeOffset]::FromUnixTimeSeconds([long]$resetValue).LocalDateTime
        Write-Host "  Resets at: $resetTime" -ForegroundColor White
    }
}

# Test 3: Check repository access
Write-Info "`n📋 Test 3: Testing repository access..."

try {
    $repoResponse = Invoke-WebRequest -Uri "https://api.github.com/repos/metanull/inventory-app" -Headers $headers -Method Get
    
    if ($repoResponse.StatusCode -eq 200) {
        Write-Success "✅ Can access repository: metanull/inventory-app"
        $repo = $repoResponse.Content | ConvertFrom-Json
        Write-Info "  Repository permissions:"
        Write-Host "    Admin: $($repo.permissions.admin)" -ForegroundColor $(if ($repo.permissions.admin) { "Green" } else { "Gray" })
        Write-Host "    Push: $($repo.permissions.push)" -ForegroundColor $(if ($repo.permissions.push) { "Green" } else { "Gray" })
        Write-Host "    Pull: $($repo.permissions.pull)" -ForegroundColor $(if ($repo.permissions.pull) { "Green" } else { "Gray" })
    }
} catch {
    Write-Warning "⚠️ Cannot access repository (might not have repo scope)"
    if ($_.Exception.Response) {
        $statusCode = $_.Exception.Response.StatusCode.value__
        Write-Warning "  HTTP Status: $statusCode"
    }
}

# Test 4: Check packages access
Write-Info "`n📋 Test 4: Testing GitHub Packages access..."

try {
    $packagesHeaders = @{
        "Authorization" = "Bearer $Token"
        "Accept" = "application/vnd.github.package-deletes-preview+json"
    }
    
    # Try to access packages for the user
    $packagesResponse = Invoke-WebRequest -Uri "https://api.github.com/user/packages?package_type=npm" -Headers $packagesHeaders -Method Get
    
    if ($packagesResponse.StatusCode -eq 200) {
        Write-Success "✅ Can access GitHub Packages"
        $packages = $packagesResponse.Content | ConvertFrom-Json
        if ($packages.Count -gt 0) {
            Write-Info "  Found $($packages.Count) package(s)"
        } else {
            Write-Info "  No packages found (but access is granted)"
        }
    }
} catch {
    Write-Warning "⚠️ Cannot access GitHub Packages"
    if ($_.Exception.Response) {
        $statusCode = $_.Exception.Response.StatusCode.value__
        Write-Warning "  HTTP Status: $statusCode"
        
        if ($statusCode -eq 403) {
            Write-Warning "  This might mean the token needs 'read:packages' or 'write:packages' scope"
        }
    }
}

# Test 5: Test Composer authentication
Write-Info "`n📋 Test 5: Testing Composer authentication..."

try {
    # Simulate what Composer does when authenticating
    $composerHeaders = @{
        "Authorization" = "token $Token"
    }
    
    $composerResponse = Invoke-WebRequest -Uri "https://api.github.com/user" -Headers $composerHeaders -Method Get
    
    if ($composerResponse.StatusCode -eq 200) {
        Write-Success "✅ Token works with Composer's authentication method"
    }
} catch {
    Write-Warning "⚠️ Token might not work with Composer"
}

# Summary and Recommendations
Write-Info "`n=========================================="
Write-Info "Summary and Recommendations"
Write-Info "=========================================="

Write-Info "`nFor your Laravel project, the token should have these scopes:"
Write-Host "  ✅ Required for Composer (private dependencies):" -ForegroundColor White
Write-Host "     • repo (full repo access)" -ForegroundColor Yellow
Write-Host "     OR" -ForegroundColor Yellow
Write-Host "     • read:packages (for GitHub Packages)" -ForegroundColor Yellow

Write-Host "`n  ✅ Required for npm (GitHub Packages):" -ForegroundColor White
Write-Host "     • read:packages" -ForegroundColor Yellow
Write-Host "     • write:packages (if publishing)" -ForegroundColor Yellow

Write-Host "`n  ✅ Recommended for workflows:" -ForegroundColor White
Write-Host "     • workflow (to trigger workflows)" -ForegroundColor Yellow
Write-Host "     • write:packages (to publish packages)" -ForegroundColor Yellow

if ($scopes) {
    Write-Info "`nYour current scopes: $($scopes -join ', ')"
    
    # Check if required scopes are present
    $hasRepo = $scopeList -contains "repo"
    $hasReadPackages = $scopeList -contains "read:packages"
    $hasWritePackages = $scopeList -contains "write:packages"
    $hasWorkflow = $scopeList -contains "workflow"
    
    # Note: write:packages implicitly includes read:packages permission
    $hasPackagesRead = $hasReadPackages -or $hasWritePackages
    
    Write-Info "`nScope Analysis:"
    Write-Host "  Repo access: $(if ($hasRepo) { '✅ Yes' } else { '❌ No' })" -ForegroundColor $(if ($hasRepo) { "Green" } else { "Red" })
    Write-Host "  Read packages: $(if ($hasPackagesRead) { '✅ Yes' } else { '❌ No' })$(if ($hasWritePackages -and -not $hasReadPackages) { ' (via write:packages)' } else { '' })" -ForegroundColor $(if ($hasPackagesRead) { "Green" } else { "Red" })
    Write-Host "  Write packages: $(if ($hasWritePackages) { '✅ Yes' } else { '❌ No' })" -ForegroundColor $(if ($hasWritePackages) { "Green" } else { "Red" })
    Write-Host "  Workflow: $(if ($hasWorkflow) { '✅ Yes' } else { '❌ No' })" -ForegroundColor $(if ($hasWorkflow) { "Green" } else { "Red" })
    
    if (-not $hasRepo -and -not $hasPackagesRead) {
        Write-Warning "`n⚠️ WARNING: Token may not work for Composer dependencies!"
        Write-Warning "You need either 'repo' or 'read:packages' (or 'write:packages') scope."
    }
    
    if (-not $hasPackagesRead) {
        Write-Warning "`n⚠️ WARNING: Token may not work for npm GitHub Packages!"
        Write-Warning "You need 'read:packages' or 'write:packages' scope."
    }
}

Write-Info "`n=========================================="
Write-Success "✅ Token verification complete!"
Write-Info "=========================================="
