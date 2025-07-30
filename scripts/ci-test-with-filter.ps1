#!/usr/bin/env pwsh
<#
    .SYNOPSIS
        Helper script for running tests with a filter
#>
[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)]
    [string]$Filter
)
End {
    Write-Information "Running tests with filter: $Filter"

    # Set environment variable and run ci-test
    $env:COMPOSER_ARGS = "--filter `"$Filter`""
    & composer ci-test

    # Clean up the environment variable after use
    Remove-Item Env:\COMPOSER_ARGS -ErrorAction SilentlyContinue
}