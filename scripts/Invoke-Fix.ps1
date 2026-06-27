#Requires -Version 7.0
<#
.SYNOPSIS
    Run auto-fix operations in the Docker dev stack.

.DESCRIPTION
    Delegates fixes to the appropriate container:
      - PHP fixes → app service  (docker compose run --rm --no-deps app)
      - npm fixes → tools service (docker compose --profile tools run --rm --no-deps tools)

.PARAMETER Fix
    Which fix to run. Defaults to 'all'.

    all                 lint-fix + dependencies-fix
    lint-fix            PHP (Pint) + Prettier auto-format
    dependencies-fix    composer update (resolves outdated/vulnerable PHP packages, updates composer.lock)
    npm-audit-fix       npm audit fix (safe fixes only)
    npm-update          npm update (update packages within semver range)

.EXAMPLE
    .\scripts\Invoke-Fix.ps1
    .\scripts\Invoke-Fix.ps1 -Fix lint-fix
    .\scripts\Invoke-Fix.ps1 -Fix npm-update
#>
param(
    [ValidateSet(
        'all',
        'lint-fix',
        'dependencies-fix',
        'npm-audit-fix',
        'npm-update'
    )]
    [string]$Fix = 'all'
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$MyScriptRoot = Split-Path -Parent $MyInvocation.MyCommand.Definition
$ProjectRoot = Split-Path $MyScriptRoot
Set-Location $ProjectRoot

function Invoke-App {
    param([string[]]$Command)
    docker compose run --rm --no-deps app @Command
    if ($LASTEXITCODE -ne 0) { throw "Fix failed: $($Command -join ' ')" }
}

function Invoke-Tools {
    param([string[]]$Command)
    docker compose --profile tools run --rm --no-deps tools @Command
    if ($LASTEXITCODE -ne 0) { throw "Fix failed: $($Command -join ' ')" }
}

switch ($Fix) {
    'all' {
        & $MyInvocation.MyCommand.Definition -Fix lint-fix
        & $MyInvocation.MyCommand.Definition -Fix dependencies-fix
    }
    'lint-fix' {
        Invoke-App @('vendor/bin/pint', '--no-interaction', '--ansi')
        Invoke-Tools @('npx', 'prettier', '--write', '--ignore-unknown', '--log-level', 'warn', './resources/**')
    }
    'dependencies-fix' {
        docker compose run --rm --no-deps --user root `
            -e COMPOSER_ALLOW_SUPERUSER=1 `
            -e COMPOSER_HOME=/tmp/composer-home `
            -e COMPOSER_CACHE_DIR=/tmp/composer-cache `
            -e COMPOSER_SOURCE_FALLBACK=1 `
            app composer update --no-scripts --with-dependencies --no-interaction --no-progress --ansi
        if ($LASTEXITCODE -ne 0) { throw "Fix failed: composer update" }
    }
    'npm-audit-fix' {
        Invoke-Tools @('npm', 'audit', 'fix')
    }
    'npm-update' {
        Invoke-Tools @('npm', 'update')
    }
}
