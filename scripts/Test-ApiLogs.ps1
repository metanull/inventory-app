<#
.SYNOPSIS
    Tests if the API log file exists and creates it if it doesn't.
.DESCRIPTION
    This script checks for the existence of the API log file and creates it if it does not
    exist. It is used to ensure that the log file is available for other scripts to read or write logs.
.PARAMETER Path
    The path to the directory where the log file should be located. Defaults to the current directory.
.EXAMPLE
    .\scripts\Test-ApiLogs.ps1
    Tests for the existence of the API log file in the current directory and creates it if it doesn't exist.
.NOTES
    This script is typically used in conjunction with other scripts that read or write to the API log file.
#>
[CmdletBinding()]
param(
    [Parameter(Mandatory=$false)]
    [ValidateScript({ Test-Path -Path $_ -PathType Container })]
    [string]$Path = '.'
)
End {
    $LogFile = Join-Path -Path $Path -ChildPath 'storage/logs/laravel.log'
    if (-not (Test-Path -Path $LogFile -PathType Leaf)) {
        Write-Information "Creating new log file at $LogFile"
        New-Item -Path $LogFile -ItemType File -ErrorAction Stop | Out-Null
    }
    Test-Path -Path $LogFile -PathType Leaf
}