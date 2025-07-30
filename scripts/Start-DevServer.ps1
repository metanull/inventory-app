#requires -module MetaNull.LaravelUtils

<#
.SYNOPSIS
    Start the Laravel Development Servers
.DESCRIPTION
    This script loads the MetaNull.LaravelUtils module and starts the Laravel development environment.
    It can reset the database, use custom ports for Laravel and Vite, and skip build steps if needed.
.PARAMETER LaravelPort
    The port on which the Laravel server will run. Default is 8000.
.PARAMETER VitePort
    The port on which the Vite server will run. Default is 5173.
.PARAMETER TimeoutSeconds
    The number of seconds to wait for the servers to start. Default is 10 seconds.
.PARAMETER Reset
    If specified, the script will reset the database before starting the servers.
.PARAMETER SkipBuild
    If specified, the script will skip the build step for the Vite server.
.PARAMETER Verbose
    If specified, the script will run in verbose mode, providing more detailed output.
.EXAMPLE
   .\scripts\Start-DevServer.ps1                    # Start development servers
   .\scripts\Start-DevServer.ps1 -Reset             # Reset database and start
   .\scripts\Start-DevServer.ps1 -LaravelPort 8001  # Use custom Laravel port
   .\scripts\Start-DevServer.ps1 -VitePort 5174     # Use custom Vite port

#>
param(
    [int]$LaravelPort = 8000,
    [int]$VitePort = 5173,
    [int]$TimeoutSeconds = 60,
    [switch]$Reset = $false,
    [switch]$SkipBuild = $false,
    [switch]$Verbose = $false
)
End {
    # Prepare parameters for Start-Laravel
    $startParams = @{
        Path = (Get-Location).Path
        WebPort = $LaravelPort
        VitePort = $VitePort
        TimeoutSeconds = $TimeoutSeconds
    }

    if ($Reset) { $startParams.Reset = $true }
    if ($SkipBuild) { $startParams.SkipBuild = $true }
    if ($Verbose) { $startParams.Verbose = $true }
    if ($SkipChecks) { $startParams.SkipChecks = $true }

    # Call the Start-Laravel function from the module
    Start-Laravel @startParams
}