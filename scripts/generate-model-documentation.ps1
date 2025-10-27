<#
.SYNOPSIS
    Generate Laravel model documentation.

.DESCRIPTION
    This script generates documentation for all Laravel models using the `php artisan docs:model` command.
    The documentation is automatically generated from model definitions and database schemas.

.PARAMETER Force
    Force regeneration of all model documentation, even if files already exist.

.EXAMPLE
    .\scripts\generate-model-documentation.ps1
    Generate model documentation

.EXAMPLE
    .\scripts\generate-model-documentation.ps1 -Force
    Force regenerate all model documentation
#>

[CmdletBinding()]
param(
    [Parameter(Mandatory=$false)]
    [switch]$Force
)

# Enable strict mode
Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

Write-Host "📚 Generating Laravel Model Documentation..." -ForegroundColor Cyan
Write-Host ""

# Build the command
$commandArgs = @("artisan", "docs:model")
if ($Force) {
    $commandArgs += "--force"
    Write-Host "  Mode: Force regeneration" -ForegroundColor Gray
} else {
    Write-Host "  Mode: Update only changed models" -ForegroundColor Gray
}
$commandArgs += "--ansi"

Write-Host "  Output: docs/_model/" -ForegroundColor Gray
Write-Host ""
Write-Host "Running: php artisan docs:model $(if ($Force) { '--force' } else { '' }) --ansi" -ForegroundColor Gray
Write-Host ""

try {
    # Run the artisan command
    & php @commandArgs
    $exitCode = $LASTEXITCODE
    
    if ($exitCode -eq 0) {
        Write-Host ""
        Write-Host "✅ Model documentation generated successfully!" -ForegroundColor Green
        Write-Host "   Output: docs/_model/" -ForegroundColor Gray
        Write-Host ""
        Write-Host "💡 Tip: Run 'php artisan docs:model --force' to regenerate all documentation" -ForegroundColor DarkGray
    } else {
        Write-Host ""
        Write-Host "❌ Model documentation generation failed with exit code: $exitCode" -ForegroundColor Red
        exit $exitCode
    }
} catch {
    Write-Host ""
    Write-Host "❌ Error running model documentation generation: $_" -ForegroundColor Red
    exit 1
}
