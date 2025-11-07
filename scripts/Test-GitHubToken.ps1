Function Test-GitHubToken {
    param (
        [Parameter(Mandatory = $true)]
        [System.Management.Automation.Credential()]
        [System.Management.Automation.PSCredential]
        $Credential
    )
    $secret = $Credential.GetNetworkCredential().password
    $request = @{
        Uri = "https://api.github.com"
        Headers = @{ "Authorization" = "token $($secret)" }
        Method = "Head"
    }
    $response = Invoke-WebRequest @request
    if ($response.StatusCode -eq 200) {
        return $response.Headers['X-OAuth-Scopes']
    } else {
        throw "Invalid GitHub Token"
    }
}
