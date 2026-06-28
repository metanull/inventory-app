$AppRoot = Split-Path -Parent -Path $PSScriptRoot
$StaticAnalysisOutput = Join-Path $AppRoot 'temp_PHPSTAN.txt'
Set-Location $AppRoot
Clear-Content $StaticAnalysisOutput -ErrorAction Continue 
docker compose run --rm app vendor/bin/phpstan analyse --no-progress --memory-limit=512M --error-format=raw --level=10 > $StaticAnalysisOutput
if (-not (Get-Content $StaticAnalysisOutput)) {
    Write-Host 'Success' -ForegroundColor Green
} else {
    Write-Warning "Static-analysis issues found: $(Resolve-Path $StaticAnalysisOutput)"
}