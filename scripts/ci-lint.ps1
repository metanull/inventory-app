#!/usr/bin/env pwsh
# ci-lint.ps1 - Wrapper script for composer ci-lint with argument passing

# Get all arguments passed to the script
$scriptArgs = $args

# Check if there are arguments from environment variable (for composer integration)
$envArgs = $env:COMPOSER_ARGS
if ($envArgs) {
    $scriptArgs += $envArgs.Split(' ')
}

# Base command with default arguments
$baseCommand = ".\vendor\bin\pint --no-interaction --ansi"

# Add any additional arguments passed to the script
if ($scriptArgs.Count -gt 0) {
    $additionalArgs = $scriptArgs -join " "
    $fullCommand = "$baseCommand $additionalArgs"
} else {
    $fullCommand = $baseCommand
}

Write-Host "Running: $fullCommand" -ForegroundColor Green

# Execute the command
Invoke-Expression $fullCommand

# Clean up the environment variable after use
Remove-Item Env:\COMPOSER_ARGS -ErrorAction SilentlyContinue
