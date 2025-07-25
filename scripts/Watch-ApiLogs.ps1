[CmdletBinding()]
param(
    [Parameter(Mandatory=$false)]
    [ValidateScript({ Test-Path -Path $_ -PathType Container })]
    [string]$Path = '.'
)
Process {
    # Test if the log file exists and create it if it doesn't
    . $PsScriptRoot\Test-ApiLogs -Path $Path | Out-Null
    
    Write-Information "Watching API logs at $LogFile"
    Get-Content -Path $LogFile -Wait
}