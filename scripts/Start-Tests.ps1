#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Run all test suites in parallel with progress tracking
.DESCRIPTION
    Runs Laravel test suites and npm tests in parallel jobs, displays progress as they complete
.PARAMETER Fast
    When set, adds --stop-on-defect to PHP tests and stops entire process on first failure
.EXAMPLE
    .\scripts\start-tests.ps1
.EXAMPLE
    .\scripts\start-tests.ps1 -Fast
#>

[CmdletBinding()]
param(
    [switch]$Fast
)

$ErrorActionPreference = "Stop"

# Start timer
$stopwatch = [System.Diagnostics.Stopwatch]::StartNew()

# Define all test suites
$laravelSuites = @('Api','Configuration','Console','Event','Integration','Unit','Web')
$allJobs = @()
$totalJobs = $laravelSuites.Count + 1  # Laravel suites + npm tests
$completedJobs = 0

Write-Host "Starting parallel test execution..." -ForegroundColor Cyan
Write-Host "Total jobs: $totalJobs" -ForegroundColor Cyan
Write-Host ""

# Start Laravel test suites as background jobs
foreach ($suite in $laravelSuites) {
    $job = Start-Job -Name "Laravel-$suite" -ScriptBlock {
        param($suiteName, $useFast)
        
        $env:DB_CONNECTION = 'sqlite'
        
        # Build command arguments
        $testArgs = @(
            'artisan', 'test',
            "--testsuite=$suiteName",
            '--parallel',
            '--compact',
            '--no-progress',
            '--disallow-test-output',
            '--fail-on-risky',
            '--fail-on-warning',
            '--fail-on-deprecation',
            '--colors=never'
            # --teamcity not supported in parallel mode
        )
        
        # Add --stop-on-defect when Fast mode is enabled
        if ($useFast) {
            $testArgs += '--stop-on-defect'
        }
        
        # Run the test suite
        & php @testArgs

        # Return exit code
        return $LASTEXITCODE
    } -ArgumentList $suite, $Fast.IsPresent
    
    $allJobs += $job
    Write-Verbose "Started job: Laravel-$suite (ID: $($job.Id))"
}

# Start npm tests as a background job
$npmJob = Start-Job -Name "Npm-Tests" -ScriptBlock {
    # Run npm tests
    & npm run test:all 2>&1
    
    # Return exit code
    return $LASTEXITCODE
}

$allJobs += $npmJob
Write-Verbose "Started job: Npm-Tests (ID: $($npmJob.Id))"

# Wait for jobs to complete with progress tracking
$results = @{}
$shouldStop = $false

while ($completedJobs -lt $totalJobs -and -not $shouldStop) {
    # Check for completed jobs
    foreach ($job in $allJobs) {
        if ($job.State -eq 'Completed' -and -not $results.ContainsKey($job.Name)) {
            $completedJobs++
            
            # Receive job output and result
            $output = Receive-Job -Job $job
            $exitCode = $output | Select-Object -Last 1
            
            # Store result
            $results[$job.Name] = @{
                ExitCode = if ($exitCode -is [int]) { $exitCode } else { 0 }
                Output = $output
            }
            
            # Update progress
            $percentComplete = [math]::Round(($completedJobs / $totalJobs) * 100)
            Write-Progress -Activity "Running Tests in Parallel" `
                          -Status "$completedJobs of $totalJobs completed" `
                          -CurrentOperation "Completed: $($job.Name)" `
                          -PercentComplete $percentComplete
            
            Write-Host "[$completedJobs/$totalJobs] " -NoNewline -ForegroundColor Gray
            
            if ($results[$job.Name].ExitCode -eq 0) {
                Write-Host "✓ $($job.Name)" -ForegroundColor Green
            } else {
                Write-Host "✗ $($job.Name)" -ForegroundColor Red
                
                # In Fast mode, stop on first failure
                if ($Fast) {
                    $shouldStop = $true
                    Write-Host "Fast mode: Stopping on first failure" -ForegroundColor Yellow
                    break
                }
            }
        }
        elseif ($job.State -eq 'Failed' -and -not $results.ContainsKey($job.Name)) {
            $completedJobs++
            
            # Store failure
            $results[$job.Name] = @{
                ExitCode = 1
                Output = "Job failed: $($job.ChildJobs[0].JobStateInfo.Reason)"
            }
            
            # Update progress
            $percentComplete = [math]::Round(($completedJobs / $totalJobs) * 100)
            Write-Progress -Activity "Running Tests in Parallel" `
                          -Status "$completedJobs of $totalJobs completed" `
                          -CurrentOperation "Failed: $($job.Name)" `
                          -PercentComplete $percentComplete
            
            Write-Host "[$completedJobs/$totalJobs] " -NoNewline -ForegroundColor Gray
            Write-Host "✗ $($job.Name) (Job Failed)" -ForegroundColor Red
            
            # In Fast mode, stop on first failure
            if ($Fast) {
                $shouldStop = $true
                Write-Host "Fast mode: Stopping on first failure" -ForegroundColor Yellow
                break
            }
        }
    }
    
    # Small delay to avoid busy-waiting
    Start-Sleep -Milliseconds 500
}

# If stopped early in Fast mode, cancel remaining jobs
if ($shouldStop) {
    Write-Host ""
    Write-Host "Cancelling remaining jobs..." -ForegroundColor Yellow
    foreach ($job in $allJobs) {
        if ($job.State -eq 'Running') {
            Stop-Job -Job $job
            Write-Verbose "Stopped job: $($job.Name)"
        }
    }
}

# Complete progress bar
Write-Progress -Activity "Running Tests in Parallel" -Completed

# Clean up jobs
$allJobs | Remove-Job -Force

# Stop timer
$stopwatch.Stop()

Write-Host ""
Write-Host ("=" * 80) -ForegroundColor Cyan
Write-Host "Test Results Summary" -ForegroundColor Cyan
Write-Host ("=" * 80) -ForegroundColor Cyan
Write-Host ""

$failedJobs = @()

foreach ($jobName in ($results.Keys | Sort-Object)) {
    $result = $results[$jobName]
    $status = if ($result.ExitCode -eq 0) { "PASS" } else { "FAIL" }
    $color = if ($result.ExitCode -eq 0) { "Green" } else { "Red" }
    
    Write-Host "  $status" -ForegroundColor $color -NoNewline
    Write-Host " : $jobName" -ForegroundColor Gray
    
    if ($result.ExitCode -ne 0) {
        $failedJobs += $jobName
    }
}

Write-Host ""

# Display execution time
$elapsed = $stopwatch.Elapsed
$timeString = if ($elapsed.TotalMinutes -ge 1) {
    "{0:N0}m {1:N0}s" -f $elapsed.TotalMinutes, $elapsed.Seconds
} else {
    "{0:N2}s" -f $elapsed.TotalSeconds
}
Write-Host "Total execution time: $timeString" -ForegroundColor Cyan
Write-Host ""

# Display failed job outputs
if ($failedJobs.Count -gt 0) {
    Write-Host ("=" * 80) -ForegroundColor Red
    Write-Host "Failed Jobs Output" -ForegroundColor Red
    Write-Host ("=" * 80) -ForegroundColor Red
    Write-Host ""
    
    foreach ($jobName in $failedJobs) {
        Write-Host "--- $jobName ---" -ForegroundColor Yellow
        Write-Host $results[$jobName].Output
        Write-Host ""
    }
    
    Write-Host ("=" * 80) -ForegroundColor Red
    Write-Host "TESTS FAILED: $($failedJobs.Count) of $totalJobs job(s) failed" -ForegroundColor Red
    Write-Host ("=" * 80) -ForegroundColor Red
    
    exit 1
}
else {
    Write-Host ("=" * 80) -ForegroundColor Green
    Write-Host "ALL TESTS PASSED!" -ForegroundColor Green
    Write-Host ("=" * 80) -ForegroundColor Green
    
    exit 0
}
