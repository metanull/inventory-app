#requires -module MetaNull.LaravelUtils

<#
.SYNOPSIS
    Test the Laravel Development Servers
.DESCRIPTION
    This script loads the MetaNull.LaravelUtils module and tests the Laravel development environment.
.PARAMETER LaravelPort
    The port on which the Laravel server will run. Default is 8000.
.PARAMETER VitePort
    The port on which the Vite server will run. Default is 5173.
.PARAMETER Verbose
    If specified, the script will run in verbose mode, providing more detailed output.
.EXAMPLE
   .\scripts\Test-DevServer.ps1                     # Test development servers
    .\scripts\Test-DevServer.ps1 -LaravelPort 8001   # Use custom Laravel port
    .\scripts\Test-DevServer.ps1 -VitePort 5174      # Use custom Vite port
#>
param(
    [int]$LaravelPort = 8000,
    [int]$VitePort = 5173,
    [switch]$Verbose = $false
)
End {
    # Prepare parameters for Test-Laravel
    $testParams = @{
        WebPort = $LaravelPort
        VitePort = $VitePort
        ErrorAction = 'Stop'  # Ensure errors are caught
    }

    if ($Verbose) { $testParams.Verbose = $true }

    # Call the Test-Laravel function from the module
    try {
        if((Test-Laravel @testParams)) {
            exit 0
        }
    } catch {
        Write-Warning "Error: $($_.Exception.Message)"
    }
    exit 1
}
