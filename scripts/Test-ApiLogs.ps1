[CmdletBinding()]
param(
    [Parameter(Mandatory=$false)]
    [ValidateScript({ Test-Path -Path $_ -PathType Container })]
    [string]$Path = '.'
)
Process {
    $LogFile = Join-Path -Path $Path -ChildPath 'storage/logs/laravel.log'
    if (-not (Test-Path -Path $LogFile -PathType Leaf)) {
        Write-Information "Creating new log file at $LogFile"
        New-Item -Path $LogFile -ItemType File -ErrorAction Stop | Out-Null
    }
    Test-Path -Path $LogFile -PathType Leaf
}