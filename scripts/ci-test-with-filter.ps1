#!/usr/bin/env pwsh
# ci-test-with-filter.ps1 - Helper script for running tests with a filter

param(
    [Parameter(Mandatory = $true)]
    [string]$Filter
)

Write-Host "Running tests with filter: $Filter" -ForegroundColor Green

# Set environment variable and run ci-test
$env:COMPOSER_ARGS = "--filter `"$Filter`""
& composer ci-test

# Clean up the environment variable after use
Remove-Item Env:\COMPOSER_ARGS -ErrorAction SilentlyContinue