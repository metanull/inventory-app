<#
.SYNOPSIS
    Watches the API logs for changes and outputs new log entries in real-time.
.DESCRIPTION
    This script monitors the API log file for any new entries and outputs them to the console in
    real-time. It is useful for debugging and monitoring API activity during development.
.PARAMETER Path
    The path to the directory where the log file is located. Defaults to the current directory.
.EXAMPLE
    .\scripts\Watch-ApiLogs.ps1
    Watches the API logs in the current directory and outputs new entries as they are added.
.NOTES
    This script is typically used in conjunction with other scripts that write to the API log file.
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

    Write-Information "Watching API logs at $LogFile"
    Get-Content -Path $LogFile -Wait
}