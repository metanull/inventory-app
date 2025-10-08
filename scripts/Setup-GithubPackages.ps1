# Setup GitHub Packages Authentication for npm
# This script configures npm to authenticate with GitHub Package Registry

Write-Host "GitHub Packages Authentication Setup" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host ""

# Check if .npmrc exists
$npmrcPath = Join-Path $PSScriptRoot ".." ".npmrc"
$npmrcExists = Test-Path $npmrcPath

if ($npmrcExists) {
    Write-Host "Found existing .npmrc file" -ForegroundColor Yellow
    $overwrite = Read-Host "Do you want to update it? (y/n)"
    if ($overwrite -ne 'y') {
        Write-Host "Aborted." -ForegroundColor Red
        exit 0
    }
}

Write-Host ""
Write-Host "You need a GitHub Personal Access Token (PAT) with 'read:packages' scope" -ForegroundColor Yellow
Write-Host "To create one:" -ForegroundColor Yellow
Write-Host "  1. Go to https://github.com/settings/tokens" -ForegroundColor Gray
Write-Host "  2. Click 'Generate new token' > 'Generate new token (classic)'" -ForegroundColor Gray
Write-Host "  3. Give it a name like 'NPM Package Access'" -ForegroundColor Gray
Write-Host "  4. Check the 'read:packages' scope" -ForegroundColor Gray
Write-Host "  5. Click 'Generate token' and copy it" -ForegroundColor Gray
Write-Host ""

$token = Read-Host "Enter your GitHub Personal Access Token" -AsSecureString
$tokenPlain = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto(
    [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($token)
)

if ([string]::IsNullOrWhiteSpace($tokenPlain)) {
    Write-Host "Error: Token cannot be empty" -ForegroundColor Red
    exit 1
}

# Create or update .npmrc
$npmrcContent = @"
@metanull:registry=https://npm.pkg.github.com
//npm.pkg.github.com/:_authToken=$tokenPlain
"@

Set-Content -Path $npmrcPath -Value $npmrcContent -NoNewline

Write-Host ""
Write-Host "✓ Successfully configured npm authentication!" -ForegroundColor Green
Write-Host ""
Write-Host "The .npmrc file has been created/updated at:" -ForegroundColor Cyan
Write-Host "  $npmrcPath" -ForegroundColor Gray
Write-Host ""
Write-Host "IMPORTANT: .npmrc is in .gitignore (it contains your token)" -ForegroundColor Yellow
Write-Host ""
Write-Host "Now you can run: npm install" -ForegroundColor Cyan
Write-Host ""

# Verify .gitignore includes .npmrc
$gitignorePath = Join-Path $PSScriptRoot ".." ".gitignore"
if (Test-Path $gitignorePath) {
    $gitignoreContent = Get-Content $gitignorePath -Raw
    if ($gitignoreContent -notmatch '\.npmrc') {
        Write-Host "WARNING: .npmrc is not in .gitignore!" -ForegroundColor Red
        Write-Host "Adding .npmrc to .gitignore..." -ForegroundColor Yellow
        Add-Content -Path $gitignorePath -Value "`n# npm authentication (contains tokens)`n.npmrc"
        Write-Host "✓ Added .npmrc to .gitignore" -ForegroundColor Green
    }
}
