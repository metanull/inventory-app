#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Run Laravel queue worker until all jobs have been processed
.DESCRIPTION
    Starts a Laravel queue worker that processes all pending jobs and exits when the queue is empty.
    Displays progress information and handles graceful shutdown.
.PARAMETER Queue
    The queue connection or specific queue name to process (default: default)
.PARAMETER Timeout
    The maximum number of seconds a job can run (default: 60)
.PARAMETER Sleep
    Number of seconds to sleep when no job is available (default: 3)
.PARAMETER Tries
    Number of times to attempt a job before logging it as failed (default: 1)
.PARAMETER MaxJobs
    The maximum number of jobs to process before stopping (optional)
.PARAMETER Memory
    The memory limit in megabytes (default: 128)
.EXAMPLE
    .\scripts\Start-QueueWorker.ps1
.EXAMPLE
    .\scripts\Start-QueueWorker.ps1 -Queue "high,default"
.EXAMPLE
    .\scripts\Start-QueueWorker.ps1 -Timeout 120 -Tries 3
.EXAMPLE
    .\scripts\Start-QueueWorker.ps1 -MaxJobs 100
#>

[CmdletBinding()]
param(
    [string]$Queue = "default",
    [int]$Timeout = 60,
    [int]$Sleep = 3,
    [int]$Tries = 1,
    [int]$MaxJobs = 0,
    [int]$Memory = 128
)

$ErrorActionPreference = "Stop"

# Resolve project root directory (parent of scripts folder)
$ProjectRoot = Split-Path -Parent $PSScriptRoot

if (-not (Test-Path $ProjectRoot)) {
    Write-Error "Project root directory not found: $ProjectRoot"
    exit 1
}

# Change to project root
Set-Location $ProjectRoot

# Verify artisan exists
if (-not (Test-Path "artisan")) {
    Write-Error "Laravel artisan file not found in: $ProjectRoot"
    exit 1
}

# Build the command arguments
$arguments = @(
    "artisan",
    "queue:work",
    "--stop-when-empty",
    "--queue=$Queue",
    "--timeout=$Timeout",
    "--sleep=$Sleep",
    "--tries=$Tries",
    "--memory=$Memory"
)

# Add max-jobs if specified
if ($MaxJobs -gt 0) {
    $arguments += "--max-jobs=$MaxJobs"
}

# Display configuration
Write-Host "Laravel Queue Worker" -ForegroundColor Cyan
Write-Host "===================" -ForegroundColor Cyan
Write-Host "Queue:      $Queue"
Write-Host "Timeout:    $Timeout seconds"
Write-Host "Sleep:      $Sleep seconds"
Write-Host "Tries:      $Tries"
Write-Host "Memory:     $Memory MB"
if ($MaxJobs -gt 0) {
    Write-Host "Max Jobs:   $MaxJobs"
}
Write-Host ""
Write-Host "Processing jobs... (will exit when queue is empty)" -ForegroundColor Yellow
Write-Host ""

# Start timer
$stopwatch = [System.Diagnostics.Stopwatch]::StartNew()

try {
    # Run the queue worker
    $process = Start-Process -FilePath "php" -ArgumentList $arguments -NoNewWindow -PassThru -Wait

    $stopwatch.Stop()
    $elapsed = $stopwatch.Elapsed

    if ($process.ExitCode -eq 0) {
        Write-Host ""
        Write-Host "Queue processing completed successfully!" -ForegroundColor Green
        Write-Host "Total time: $($elapsed.ToString('hh\:mm\:ss'))" -ForegroundColor Cyan
        exit 0
    } else {
        Write-Host ""
        Write-Host "Queue worker exited with code: $($process.ExitCode)" -ForegroundColor Red
        Write-Host "Total time: $($elapsed.ToString('hh\:mm\:ss'))" -ForegroundColor Cyan
        exit $process.ExitCode
    }
} catch {
    $stopwatch.Stop()
    Write-Host ""
    Write-Host "Error running queue worker: $_" -ForegroundColor Red
    exit 1
}
