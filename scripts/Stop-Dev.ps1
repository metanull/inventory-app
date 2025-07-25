# Stop-Dev.ps1 - Stop Development Environment
#
# This PowerShell script stops the Laravel and Vite development servers by targeting
# specific processes running on the development ports
#
# Usage:
#   .\scripts\Stop-Dev.ps1                      # Stop servers on default ports (8000, 5173)
#   .\scripts\Stop-Dev.ps1 -LaravelPort 8001    # Stop custom Laravel port
#   .\scripts\Stop-Dev.ps1 -VitePort 5174       # Stop custom Vite port
#   .\scripts\Stop-Dev.ps1 -Force               # Force stop without confirmation

param(
    [int]$LaravelPort = 8000,
    [int]$VitePort = 5173,
    [switch]$Force = $false,
    [switch]$Verbose = $false
)

# Colors for output
$ColorSuccess = "Green"
$ColorWarning = "Yellow" 
$ColorError = "Red"
$ColorInfo = "Cyan"
$ColorStep = "Magenta"

function Write-Step {
    param([string]$Message)
    Write-Host "🚀 $Message" -ForegroundColor $ColorStep
}

function Write-Success {
    param([string]$Message)
    Write-Host "✅ $Message" -ForegroundColor $ColorSuccess
}

function Write-Warning {
    param([string]$Message)
    Write-Host "⚠️  $Message" -ForegroundColor $ColorWarning
}

function Write-Info {
    param([string]$Message)
    Write-Host "ℹ️  $Message" -ForegroundColor $ColorInfo
}

function Write-DevError {
    param([string]$Message)
    Write-Host "❌ $Message" -ForegroundColor $ColorError
}

function Test-Port {
    param([int]$Port)
    try {
        $connection = Test-NetConnection -ComputerName "localhost" -Port $Port -WarningAction SilentlyContinue -InformationLevel Quiet
        return $connection
    } catch {
        return $false
    }
}

function Stop-DevServer {
    param(
        [int]$Port,
        [string]$ServerName,
        [switch]$Force
    )
    
    try {
        if (-not (Test-Port -Port $Port)) {
            Write-Info "$ServerName server is not running on port $Port"
            return $true
        }
        
        # Get processes using the port
        $processes = Get-NetTCPConnection -LocalPort $Port -ErrorAction SilentlyContinue | 
                    Select-Object -ExpandProperty OwningProcess -Unique
        
        if (-not $processes) {
            Write-Warning "Port $Port appears to be in use but no processes found"
            return $false
        }
        
        $stoppedAny = $false
        
        foreach ($processId in $processes) {
            if ($processId -and $processId -ne 0) {
                $process = Get-Process -Id $processId -ErrorAction SilentlyContinue
                if ($process) {
                    # Verify this is likely a development server process
                    $isDevProcess = $false
                    
                    switch ($ServerName) {
                        "Laravel" {
                            $isDevProcess = $process.ProcessName -match "(php|artisan)" -or 
                                          $process.CommandLine -match "artisan serve"
                        }
                        "Vite" {
                            $isDevProcess = $process.ProcessName -match "(node|npm)" -or 
                                          $process.CommandLine -match "(vite|npm.*dev)"
                        }
                    }
                    
                    if ($isDevProcess -or $Force) {
                        if (-not $Force) {
                            $confirmation = Read-Host "Stop $ServerName process '$($process.Name)' (PID: $processId)? [Y/n]"
                            if ($confirmation -eq "n" -or $confirmation -eq "N") {
                                Write-Info "Skipping process $processId"
                                continue
                            }
                        }
                        
                        Write-Step "Stopping $ServerName process '$($process.Name)' (PID: $processId) on port $Port"
                        
                        try {
                            # Try graceful stop first
                            $process.CloseMainWindow()
                            Start-Sleep -Seconds 2
                            
                            # Check if process is still running
                            $runningProcess = Get-Process -Id $processId -ErrorAction SilentlyContinue
                            if ($runningProcess) {
                                # Force kill if still running
                                Stop-Process -Id $processId -Force
                                Start-Sleep -Seconds 1
                            }
                            
                            Write-Success "Stopped $ServerName server (PID: $processId)"
                            $stoppedAny = $true
                            
                        } catch {
                            Write-DevError "Failed to stop process $processId`: $($_.Exception.Message)"
                        }
                    } else {
                        if ($Verbose) {
                            Write-Warning "Process '$($process.Name)' (PID: $processId) on port $Port doesn't appear to be a $ServerName server"
                            Write-Info "Use -Force to stop it anyway"
                        }
                    }
                }
            }
        }
        
        # Verify port is now free
        Start-Sleep -Seconds 1
        if (-not (Test-Port -Port $Port)) {
            if ($stoppedAny) {
                Write-Success "$ServerName server stopped successfully"
            }
            return $true
        } else {
            Write-Warning "$ServerName port $Port is still in use after attempting to stop processes"
            return $false
        }
        
    } catch {
        Write-DevError "Failed to stop $ServerName server on port $Port`: $($_.Exception.Message)"
        return $false
    }
}

function Stop-PowerShellJobs {
    Write-Step "Stopping any PowerShell background jobs..."
    
    $jobs = Get-Job | Where-Object { 
        $_.State -eq "Running" -and 
        ($_.Command -match "artisan serve" -or $_.Command -match "vite" -or $_.Command -match "npm.*dev")
    }
    
    if ($jobs) {
        foreach ($job in $jobs) {
            Write-Info "Stopping job: $($job.Name) (ID: $($job.Id))"
            Stop-Job $job -ErrorAction SilentlyContinue
            Remove-Job $job -ErrorAction SilentlyContinue
        }
        Write-Success "Stopped $($jobs.Count) background job(s)"
    } else {
        Write-Info "No relevant PowerShell jobs found"
    }
}

# Main execution
try {
    Write-Host "🛑 Stopping Development Environment" -ForegroundColor $ColorStep
    Write-Host "===================================" -ForegroundColor $ColorStep
    Write-Host ""
    
    if (-not $Force) {
        Write-Info "This will stop Laravel and Vite development servers"
        Write-Info "Ports to check: Laravel ($LaravelPort), Vite ($VitePort)"
        Write-Host ""
    }
    
    # Stop PowerShell background jobs first
    Stop-PowerShellJobs
    Write-Host ""
    
    # Stop individual servers
    $laravelStopped = Stop-DevServer -Port $LaravelPort -ServerName "Laravel" -Force:$Force
    $viteStopped = Stop-DevServer -Port $VitePort -ServerName "Vite" -Force:$Force
    
    Write-Host ""
    
    # Summary
    if ($laravelStopped -and $viteStopped) {
        Write-Success "Development environment stopped successfully!"
    } elseif ($laravelStopped -or $viteStopped) {
        Write-Warning "Development environment partially stopped"
        Write-Info "Some servers may still be running - check with 'composer test-dev'"
        exit 1
    } else {
        Write-DevError "Failed to stop development environment"
        Write-Info "You may need to manually stop processes or use -Force flag"
        exit 2
    }
    
} catch {
    Write-DevError "Error stopping development environment: $($_.Exception.Message)"
    if ($Verbose) {
        Write-Host $_.Exception.StackTrace -ForegroundColor $ColorError
    }
    exit 3
}
