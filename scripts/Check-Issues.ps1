<#
.SYNOPSIS
    Check for issues in the project using various tools
.DESCRIPTION
    This script runs a series of checks on the project to identify formatting, linting, type issues, and build errors.
    It generates a report summarizing the results of each check.
#>
[CmdletBinding()]
[Diagnostics.CodeAnalysis.SuppressMessage("PSAvoidUsingPositionalParameters", "", Justification = "Positional parameters are required for compatibility with npx CLI commands.")]
param()

Function headline([int]$level, [string]$message) {
    $mdTitle = ''.PadRight($level, '#')
    "`n$mdTitle $message`n"
}

$stages = @()
Write-Information "Checking for issues in the project..."

# Create a header for the issues file
Write-Output -InputObject (headline 1 "Issues Report")
Write-Output -InputObject "Generated on: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")"

# Run Composer diagnose
Write-Information "Running Composer diagnose..."
Write-Output -InputObject (headline 2 "Composer Diagnose")
$stage = [PSCustomObject]@{Name = "Composer Diagnose"; Status = $null; StartTime = (Get-Date); EndTime = (Get-Date) }
composer diagnose 2>&1
$stage.Status = $LASTEXITCODE
$stage.EndTime = (Get-Date)
$stages += $stage

# Run Composer check
Write-Information "Running Composer check..."
Write-Output -InputObject (headline 2 "Composer Issues")
$stage = [PSCustomObject]@{Name = "Composer Check"; Status = $null; StartTime = (Get-Date); EndTime = $null }
composer validate --with-dependencies --strict --quiet 2>&1
$stage.Status = $LASTEXITCODE
$stage.EndTime = (Get-Date)
$stages += $stage

# Run format check (prettier)
Write-Information "Running format check..."
Write-Output -InputObject (headline 2 "Format Issues")
$stage = [PSCustomObject]@{Name = "Format Check"; Status = $null; StartTime = (Get-Date); EndTime = $null }
npx prettier --log-level warn --no-color --check resources/js/ 2>&1
$stage.Status = $LASTEXITCODE
$stage.EndTime = (Get-Date)
$stages += $stage

# Run linting (eslint)
Write-Information "Running lint check..."
Write-Output -InputObject (headline 2 "Lint Issues")
$stage = [PSCustomObject]@{Name = "Lint Check"; Status = $null; StartTime = (Get-Date); EndTime = $null }
npx eslint --quiet --no-fix --format stylish 2>&1
$stage.Status = $LASTEXITCODE
$stage.EndTime = (Get-Date)
$stages += $stage

# Run type-check (vue-tsc)
Write-Information "Running type check..."
Write-Output -InputObject (headline 2 "Type Issues")
$stage = [PSCustomObject]@{Name = "Type Check"; Status = $LASTEXITCODE; StartTime = (Get-Date); EndTime = (Get-Date) }
npx vue-tsc --noEmit --pretty false 2>&1
$stage.Status = $LASTEXITCODE
$stage.EndTime = (Get-Date)
$stages += $stage

# Run build (vite build)
Write-Information "Running build check..."
Write-Output -InputObject (headline 2 "Build Issues")
$stage = [PSCustomObject]@{Name = "Build Check"; Status = $null; StartTime = (Get-Date); EndTime = $null }
npm run build 2>&1
$stage.Status = $LASTEXITCODE
$stage.EndTime = (Get-Date)
$stages += $stage

