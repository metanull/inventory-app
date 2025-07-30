<#
.SYNOPSIS
    Clears the API logs by emptying the log file.

.DESCRIPTION
    This script clears the API logs by emptying the log file at the specified path.

.PARAMETER Path
    The path to the directory containing the log file. Defaults to the current directory.
.EXAMPLE
    .\scripts\Clear-ApiLogs.ps1
    Clears the API logs in the current directory.
#>
[CmdletBinding()]
param(
    [Parameter(Mandatory=$false)]
    [ValidateScript({ Test-Path -Path $_ -PathType Container })]
    [string]$Path = '.'
)
End {
    # Test if the log file exists and create it if it doesn't
    . $PsScriptRoot\Test-ApiLogs -Path $Path | Out-Null

    Write-Information "Clearing existing log file at $LogFile"
    Clear-Content -Path $LogFile -ErrorAction Stop
}