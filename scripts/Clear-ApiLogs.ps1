[CmdletBinding()]
param(
    [Parameter(Mandatory=$false)]
    [ValidateScript({ Test-Path -Path $_ -PathType Container })]
    [string]$Path = '.'
)
Process {
    # Test if the log file exists and create it if it doesn't
    . $PsScriptRoot\Test-ApiLogs -Path $Path | Out-Null
    
    Write-Information "Clearing existing log file at $LogFile"
    Clear-Content -Path $LogFile -ErrorAction Stop
}