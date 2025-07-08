#!/usr/bin/env pwsh
# ci-lint-with-args.ps1 - Helper script for running lint with arguments

param(
    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]]$Args
)

Write-Host "Running lint with args: $($Args -join ' ')" -ForegroundColor Green

# Set environment variable and run ci-lint
$env:COMPOSER_ARGS = $Args -join ' '
& composer ci-lint

# Clean up the environment variable after use
Remove-Item Env:\COMPOSER_ARGS -ErrorAction SilentlyContinue