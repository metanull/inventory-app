$AppRoot = Split-Path -Parent -Path $PSScriptRoot
$TestSuites = @(                                                                                                                 
    'Unit'                                   
    'Api'
    'Web'
    'Filament'
    'Configuration'
    'Console'
    'Event'
    'Integration'
)
$TestSuites | Foreach-Object {
    $TestSuite = $_
    $LogFile = Join-Path $AppRoot "temp_PHPTEST_$($TestSuite).xml"
    Clear-Content $LogFile -ErrorAction Continue
    docker compose run --rm app php -d memory_limit=-1 artisan test --compact --log-junit=$LogFile --no-coverage --testsuite $TestSuite
    Write-Warning "Test suite '$TestSuite' completed. Log file: $(Resolve-Path $LogFile)"
}