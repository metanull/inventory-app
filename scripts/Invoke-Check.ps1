#Requires -Version 7.0
<#
.SYNOPSIS
    Run quality checks in the Docker dev stack.

.DESCRIPTION
    Delegates checks to the appropriate container:
      - PHP checks  → app service  (docker compose run --rm --no-deps app)
      - npm checks  → tools service (docker compose --profile tools run --rm --no-deps tools)

.PARAMETER Check
    Which check to run. Defaults to 'all'.

    all                 lint + static-analysis + test
    lint                PHP (Pint) + Prettier format check
    static-analysis     PHPStan static analysis
    test                Unit + Api + Web + Filament suites (parallel)
    test-unit           Unit suite only
    test-api            Api suite only
    test-web            Web suite only
    test-filament       Filament suite only
    test-configuration  Configuration suite only
    test-console        Console suite only
    test-event          Event suite only
    test-integration    Integration suite only
    test-all            All test suites (parallel)
    npm-audit           npm audit --audit-level high
    dependencies        composer audit + npm audit

.EXAMPLE
    .\scripts\Invoke-Check.ps1
    .\scripts\Invoke-Check.ps1 -Check lint
    .\scripts\Invoke-Check.ps1 -Check test-unit
#>
param(
    [ValidateSet(
        'all',
        'lint',
        'static-analysis',
        'test',
        'test-unit',
        'test-api',
        'test-web',
        'test-filament',
        'test-configuration',
        'test-console',
        'test-event',
        'test-integration',
        'test-all',
        'npm-audit',
        'dependencies'
    )]
    [string]$Check = 'all'
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$MyScriptRoot = Split-Path -Parent $MyInvocation.MyCommand.Definition
$ProjectRoot = Split-Path $MyScriptRoot
Set-Location $ProjectRoot

function Invoke-App {
    param([string[]]$Command)
    docker compose run --rm --no-deps app @Command
    if ($LASTEXITCODE -ne 0) { throw "Check failed: $($Command -join ' ')" }
}

function Invoke-Tools {
    param([string[]]$Command)
    docker compose --profile tools run --rm --no-deps tools @Command
    if ($LASTEXITCODE -ne 0) { throw "Check failed: $($Command -join ' ')" }
}

function Invoke-PhpTest {
    param([string]$Suite)
    $args = @('php', 'artisan', 'test', '--testsuite', $Suite, '--compact', '--parallel', '--no-ansi', '--stop-on-failure')
    Invoke-App $args
}

switch ($Check) {
    'all' {
        & $MyInvocation.MyCommand.Definition -Check lint
        & $MyInvocation.MyCommand.Definition -Check static-analysis
        & $MyInvocation.MyCommand.Definition -Check test
    }
    'lint' {
        Invoke-App @('vendor/bin/pint', '--no-interaction', '--ansi', '--test')
        Invoke-Tools @('npx', 'prettier', '--check', '--ignore-unknown', '--log-level', 'warn', './resources/**')
    }
    'static-analysis' {
        Invoke-App @('vendor/bin/phpstan', 'analyse', '--memory-limit=512M', '--ansi')
    }
    'test' {
        foreach ($suite in @('Unit', 'Api', 'Web', 'Filament')) {
            Invoke-PhpTest $suite
        }
    }
    'test-unit'          { Invoke-PhpTest 'Unit' }
    'test-api'           { Invoke-PhpTest 'Api' }
    'test-web'           { Invoke-PhpTest 'Web' }
    'test-filament'      { Invoke-PhpTest 'Filament' }
    'test-configuration' { Invoke-PhpTest 'Configuration' }
    'test-console'       { Invoke-PhpTest 'Console' }
    'test-event'         { Invoke-PhpTest 'Event' }
    'test-integration'   { Invoke-App @('php', 'artisan', 'test', '--testsuite', 'Integration', '--compact', '--no-ansi', '--stop-on-failure') }
    'test-all'           { Invoke-App @('php', 'artisan', 'test', '--compact', '--parallel', '--no-ansi', '--stop-on-failure') }
    'npm-audit'          { Invoke-Tools @('npm', 'audit', '--audit-level', 'moderate') }
    'dependencies' {
        Invoke-App @('composer', 'validate', '--with-dependencies', '--strict', '--ansi')
        Invoke-App @('composer', 'audit', '--format=summary')
        Invoke-Tools @('npm', 'audit', '--audit-level', 'moderate')
    }
}
