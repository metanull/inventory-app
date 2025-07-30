#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Wrapper script for composer ci-lint with argument passing
.DESCRIPTION
    This script collects arguments passed to it and combines them with any existing environment variables.
#>
[CmdletBinding()]
[Diagnostics.CodeAnalysis.SuppressMessage("PSAvoidUsingInvokeExpression", "", Justification = "Invoke-Expression is used for dynamic command execution.")]
param()
End {
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
    }
    else {
        $fullCommand = $baseCommand
    }

    Write-Information "Running: $fullCommand"

    # Execute the command
    Invoke-Expression $fullCommand

    # Clean up the environment variable after use
    Remove-Item Env:\COMPOSER_ARGS -ErrorAction SilentlyContinue
}