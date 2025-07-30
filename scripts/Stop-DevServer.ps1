#requires -module MetaNull.LaravelUtils

<#
.SYNOPSIS
    Stop the Laravel Development Servers
.DESCRIPTION
    This script loads the MetaNull.LaravelUtils module and stops the Laravel development environment.
.PARAMETER LaravelPort
    The port on which the Laravel server is running. Default is 8000.
.PARAMETER VitePort
    The port on which the Vite server is running. Default is 5173.
.PARAMETER Verbose
    If specified, the script will run in verbose mode, providing more detailed output.
.EXAMPLE
   .\scripts\Stop-DevServer.ps1                     # Stop development servers
    .\scripts\Stop-DevServer.ps1 -LaravelPort 8001   # Stop servers on custom Laravel port
    .\scripts\Stop-DevServer.ps1 -VitePort 5174      # Stop servers on custom Vite port
#>
param(
    [int]$LaravelPort = 8000,
    [int]$VitePort = 5173,
    [switch]$Verbose = $false
)
End {
    # Prepare parameters for Stop-Laravel
    $stopParams = @{
        WebPort = $LaravelPort
        VitePort = $VitePort
    }

    if ($Verbose) { $stopParams.Verbose = $true }

    # Call the Stop-Laravel function from the module
    Stop-Laravel @stopParams
}