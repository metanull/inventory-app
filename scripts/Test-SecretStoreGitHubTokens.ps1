[CmdletBinding()]
param(
    [Parameter(ValueFromPipeline, ValueFromPipelineByPropertyName, Mandatory=$false)]
    [Alias('Vault')]
    [string]
    $Name = 'MySecretVault',

    [Parameter(Mandatory=$false)]
    [string]
    $Filter = 'github-*'
)
# Load the Test-GitHubToken function
if (-not (Get-Command Test-GitHubToken -ErrorAction SilentlyContinue)) {
    $scriptPath = Join-Path -Path $PSScriptRoot -ChildPath 'Test-GitHubToken.ps1'
    . $scriptPath
}
Get-SecretInfo -Vault $Name | Where-Object { $_.Name -like $Filter } | ForEach-Object {
    try {
        $SecretName = $_.Name
        Test-GitHubToken -Credential (Get-Secret -Vault $Name -Name $_.Name) | ForEach-Object {
            Write-Host "Vault: $Name, Secret: $SecretName, Scope: $_"
        }
    } catch {
        Write-Warning "Vault: $Name, Secret: $SecretName, Scope: none (invalid token)"
    }
}
