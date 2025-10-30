#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Run all test suites in parallel with progress tracking
.DESCRIPTION
    Runs Laravel test suites and npm tests in parallel jobs, displays progress as they complete
.PARAMETER Fast
    When set, adds --stop-on-defect to PHP tests and stops entire process on first failure
.PARAMETER MaxConcurrentJobs
    Maximum number of concurrent jobs to run (default: available logical processors - 1, minimum 1)
.PARAMETER WithParallelNpmTests
    When set, npm tests run in parallel by directory (each __tests__ directory is a separate job).
    If not set, npm tests run as a single job using 'npm run test'
.PARAMETER WithParallelLaravelTests
    When set, Laravel tests run in parallel by testsuite (each testsuite is a separate job).
    If not set, Laravel tests run as a single job using 'php artisan test' without testsuite filtering
.EXAMPLE
    .\scripts\start-tests.ps1
.EXAMPLE
    .\scripts\start-tests.ps1 -Fast
.EXAMPLE
    .\scripts\start-tests.ps1 -MaxConcurrentJobs 4
.EXAMPLE
    .\scripts\start-tests.ps1 -WithParallelNpmTests
.EXAMPLE
    .\scripts\start-tests.ps1 -WithParallelLaravelTests
.EXAMPLE
    .\scripts\start-tests.ps1 -WithParallelNpmTests -WithParallelLaravelTests
#>

[CmdletBinding()]
param(
    [switch]$Fast,
    [int]$MaxConcurrentJobs = 0,  # 0 means auto-detect based on available CPU threads
    [switch]$WithParallelNpmTests,  # If set, run npm tests in parallel by directory; if not, run as single job
    [switch]$WithParallelLaravelTests  # If set, run Laravel tests in parallel by testsuite; if not, run as single job
)

$ErrorActionPreference = "Stop"

# Set MaxConcurrentJobs to available threads if 0 is passed
if ($MaxConcurrentJobs -eq 0) {
    $availableThreads = (Get-CimInstance Win32_ComputerSystem).NumberOfLogicalProcessors
    $MaxConcurrentJobs = [Math]::Max(1, "$availableThreads")
}

# Start timer
$stopwatch = [System.Diagnostics.Stopwatch]::StartNew()

# Discover Laravel test suites dynamically (directories under ./tests that contain *Test.php)
# Only used if WithParallelLaravelTests is set
if ($WithParallelLaravelTests) {
    $laravelSuites = Get-ChildItem -Directory .\tests -ErrorAction SilentlyContinue |
        Where-Object { (Get-ChildItem -Path $_.FullName -Recurse -Filter '*Test.php' -ErrorAction SilentlyContinue | Measure-Object).Count -gt 0 } |
        ForEach-Object { $_.Name }
} else {
    # If not using parallel Laravel tests, create a single job that runs all Laravel tests
    $laravelSuites = @()
}

# Discover npm vitest __tests__ directories under resources/js that contain TypeScript test files
# Only used if WithParallelNpmTests is set
if ($WithParallelNpmTests) {
    $npmTestDirs = Get-ChildItem -Recurse -Directory resources\js -Filter '__tests__' -ErrorAction SilentlyContinue |
        Where-Object { (Get-ChildItem -Path $_.FullName -Recurse -Filter '*.test.ts' -ErrorAction SilentlyContinue | Measure-Object).Count -gt 0 } |
        ForEach-Object { $_.FullName.Replace((Get-Location).Path + '\', '') }
} else {
    # If not using parallel npm tests, create a single job that runs all npm tests
    $npmTestDirs = @()
}

# Build an interleaved list of jobs (Laravel, Npm) for nicer parallel scheduling
$allTestSpecs = @()
$maxCount = [Math]::Max($laravelSuites.Count, $npmTestDirs.Count)
for ($i = 0; $i -lt $maxCount; $i++) {
    if ($i -lt $laravelSuites.Count) {
        $allTestSpecs += @{ Type = 'Laravel'; Name = $laravelSuites[$i] }
    }
    if ($i -lt $npmTestDirs.Count) {
        $allTestSpecs += @{ Type = 'Npm'; Name = $npmTestDirs[$i] }
    }
}

# If not using parallel Laravel tests, add a single Laravel job to the specs
if (-not $WithParallelLaravelTests -and $laravelSuites.Count -eq 0) {
    $allTestSpecs += @{ Type = 'Laravel'; Name = 'all' }
}

# If not using parallel npm tests, add a single npm job to the specs
if (-not $WithParallelNpmTests -and $npmTestDirs.Count -eq 0) {
    $allTestSpecs += @{ Type = 'Npm'; Name = 'all' }
}

$allJobs = @()
$totalJobs = $allTestSpecs.Count
$completedJobs = 0
$maxConcurrent = $MaxConcurrentJobs
$jobsRunning = @()

Write-Information "Starting parallel test execution..."
Write-Verbose "Total jobs: $totalJobs"
Write-Debug "Max concurrent jobs: $maxConcurrent"

# Create script blocks for job types
$laravelScriptBlock = {
    param($suiteName, $useFast)
    
    # Set current process to below-normal priority to avoid resource contention
    [System.Diagnostics.Process]::GetCurrentProcess().PriorityClass = 'BelowNormal'
    
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
}

$npmScriptBlock = {
    param($testPath)
    
    # Set current process to below-normal priority to avoid resource contention
    [System.Diagnostics.Process]::GetCurrentProcess().PriorityClass = 'BelowNormal'
    
    # Run npm tests for specific directory
    # Use --passWithNoTests to not fail when no tests are found in the directory
    & npm run test -- $testPath 2>&1
    
    # Return exit code
    return $LASTEXITCODE
}

$npmSingleScriptBlock = {
    # Set current process to below-normal priority to avoid resource contention
    [System.Diagnostics.Process]::GetCurrentProcess().PriorityClass = 'BelowNormal'
    
    # Run all npm tests without directory filtering
    & npm run test 2>&1
    
    # Return exit code
    return $LASTEXITCODE
}

$laravelSingleScriptBlock = {
    param($useFast)
    
    # Set current process to below-normal priority to avoid resource contention
    [System.Diagnostics.Process]::GetCurrentProcess().PriorityClass = 'BelowNormal'
    
    $env:DB_CONNECTION = 'sqlite'
    
    # Build command arguments (run all tests without testsuite filtering)
    $testArgs = @(
        'artisan', 'test',
        '--parallel',
        '--compact',
        '--no-progress',
        '--disallow-test-output',
        '--fail-on-risky',
        '--fail-on-warning',
        '--fail-on-deprecation',
        '--colors=never'
    )
    
    # Add --stop-on-defect when Fast mode is enabled
    if ($useFast) {
        $testArgs += '--stop-on-defect'
    }
    
    # Run all test suites
    & php @testArgs
    
    # Return exit code
    return $LASTEXITCODE
}

# Function to get display name for npm test directory
function Get-NpmTestDisplayName {
    param($testPath)
    
    # Extract parent directory relative to resources/js
    # resources/js/__tests__ -> /
    # resources/js/api/__tests__ -> /api
    # resources/js/components/__tests__ -> /components
    $parentDir = Split-Path -Parent $testPath  # Remove __tests__
    $resourcesRoot = (Resolve-Path 'resources\js').Path
    $relative = $parentDir.Replace($resourcesRoot, '').TrimStart('\','/')
    if ([string]::IsNullOrWhiteSpace($relative)) {
        return '/'
    }
    # normalize backslashes to forward slashes
    $relative = $relative -replace '\\','/'
    return '/' + $relative
}

Write-Progress -Activity "Running Tests in Parallel" `
    -Status "0 of $totalJobs completed" `
    -CurrentOperation "Initializing Jobs" `
    -PercentComplete 0

# Start initial batch of jobs
$jobIndex = 0
for ($i = 0; $i -lt $maxConcurrent -and $i -lt $totalJobs; $i++) {
    $spec = $allTestSpecs[$jobIndex]
    
    if ($spec.Type -eq 'Laravel') {
        if ($spec.Name -eq 'all') {
            # Single Laravel job for all tests (when WithParallelLaravelTests is not set)
            $job = Start-Job -Name "Laravel > All" -ScriptBlock $laravelSingleScriptBlock -ArgumentList $Fast.IsPresent
        } else {
            # Individual Laravel testsuite job
            $job = Start-Job -Name "Laravel > $($spec.Name)" -ScriptBlock $laravelScriptBlock -ArgumentList $spec.Name, $Fast.IsPresent
        }
    } else {
        if ($spec.Name -eq 'all') {
            # Single npm job for all tests (when WithParallelNpmTests is not set)
            $job = Start-Job -Name "Npm > All" -ScriptBlock $npmSingleScriptBlock
        } else {
            # Individual npm test directory job
            $displayName = Get-NpmTestDisplayName -testPath $spec.Name
            $job = Start-Job -Name "Npm > $displayName" -ScriptBlock $npmScriptBlock -ArgumentList $spec.Name
        }
    }
    
    $jobsRunning += $job
    $allJobs += $job
    Write-Verbose "Started job: $($job.Name) (ID: $($job.Id))"
    $jobIndex++
}

Write-Progress -Activity "Running Tests in Parallel" `
    -Status "0 of $totalJobs completed" `
    -CurrentOperation "Running Tests" `
    -PercentComplete 1

# Wait for jobs to complete with progress tracking
$results = @{}
$shouldStop = $false

try {
    while ($completedJobs -lt $totalJobs -and -not $shouldStop) {
        # Check for completed jobs
        foreach ($job in @($jobsRunning)) {  # Wrap in @() to ensure array
            if ($job.State -eq 'Completed' -or $job.State -eq 'Failed') {
                if (-not $results.ContainsKey($job.Name)) {
                    $completedJobs++
                    $jobsRunning = $jobsRunning | Where-Object { $_.Id -ne $job.Id }
                    
                    # Receive job output and result
                    $output = Receive-Job -Job $job
                    $exitCode = if ($job.State -eq 'Failed') { 1 } else { $output | Select-Object -Last 1 }
                    
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
                    
                    Write-Information "[$completedJobs/$totalJobs]"

                    # Execution time
                    $elapsed = $stopwatch.Elapsed
                    $timeString = if ($elapsed.TotalMinutes -ge 1) {
                        "{0:N0}m {1:N0}s" -f $elapsed.TotalMinutes, $elapsed.Seconds
                    } else {
                        "{0:N2}s" -f $elapsed.TotalSeconds
                    }
                    
                    # Display Job result
                    if ($results[$job.Name].ExitCode -eq 0) {
                        Write-Information "✓ $($job.Name) ($timeString)"
                    } else {
                        Write-Warning "✗ $($job.Name) ($timeString)"
                        
                        # In Fast mode, stop on first failure
                        if ($Fast) {
                            $shouldStop = $true
                            Write-Warning "Fast mode: Stopping on first failure"
                        }
                    }
                    
                    # Start next job if there are more to run and we haven't stopped
                    if ($jobIndex -lt $totalJobs -and -not $shouldStop) {
                        $spec = $allTestSpecs[$jobIndex]
                        
                        if ($spec.Type -eq 'Laravel') {
                            if ($spec.Name -eq 'all') {
                                # Single Laravel job for all tests (when WithParallelLaravelTests is not set)
                                $newJob = Start-Job -Name "Laravel > All" -ScriptBlock $laravelSingleScriptBlock -ArgumentList $Fast.IsPresent
                            } else {
                                # Individual Laravel testsuite job
                                $newJob = Start-Job -Name "Laravel > $($spec.Name)" -ScriptBlock $laravelScriptBlock -ArgumentList $spec.Name, $Fast.IsPresent
                            }
                        } else {
                            if ($spec.Name -eq 'all') {
                                # Single npm job for all tests (when WithParallelNpmTests is not set)
                                $newJob = Start-Job -Name "Npm > All" -ScriptBlock $npmSingleScriptBlock
                            } else {
                                # Individual npm test directory job
                                $displayName = Get-NpmTestDisplayName -testPath $spec.Name
                                $newJob = Start-Job -Name "Npm > $displayName" -ScriptBlock $npmScriptBlock -ArgumentList $spec.Name
                            }
                        }
                        
                        $jobsRunning += $newJob
                        $allJobs += $newJob
                        Write-Verbose "Started job: $($newJob.Name) (ID: $($newJob.Id))"
                        $jobIndex++
                    }
                }
            }
        }
        
        # Small delay to avoid busy-waiting
        Start-Sleep -Milliseconds 500
    }
}
catch {
    # On any exception (including user interrupt), stop running jobs immediately
    Write-Warning "Interrupted or error: $($_.Exception.Message)"
    $shouldStop = $true
}

# If stopped early (Fast mode or CTRL+C), cancel remaining jobs
if ($shouldStop) {
    Write-Verbose "Cancelling remaining jobs..."
    $jobsRunning | Stop-Job -ErrorAction SilentlyContinue
}

# Complete progress bar
Write-Progress -Activity "Running Tests in Parallel" -Completed

# Clean up jobs
$allJobs | Remove-Job -Force

# Stop timer
$stopwatch.Stop()

# Display execution time
$elapsed = $stopwatch.Elapsed
$timeString = if ($elapsed.TotalMinutes -ge 1) {
    "{0:N0}m {1:N0}s" -f $elapsed.TotalMinutes, $elapsed.Seconds
} else {
    "{0:N2}s" -f $elapsed.TotalSeconds
}
Write-Information "Total execution time: $timeString"

# Display failed job outputs
$failedJobs = @()
foreach ($jobName in ($results.Keys | Sort-Object)) {
    if ($results[$jobName].ExitCode -ne 0) {
        $failedJobs += $jobName
    }
}

if ($failedJobs.Count -gt 0) {
    Write-Verbose ("=" * 80)
    Write-Verbose "Failed Jobs Output"
    Write-Verbose ("=" * 80)
    
    foreach ($jobName in $failedJobs) {
        Write-Information "--- $jobName ---"
        Write-Output $results[$jobName].Output
    }

    Write-Warning "$($failedJobs.Count) of $totalJobs job(s) failed"
    exit 1
}
else {
    Write-Information "ALL TESTS PASSED!"
    
    exit 0
}
