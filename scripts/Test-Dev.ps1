# Test-Dev.ps1 - Check Development Environment Status
#
# This PowerShell script checks if the Laravel and Vite development servers are running
#
# Usage:
#   .\scripts\Test-Dev.ps1                      # Check default ports (8000, 5173)
#   .\scripts\Test-Dev.ps1 -LaravelPort 8001    # Check custom Laravel port
#   .\scripts\Test-Dev.ps1 -VitePort 5174       # Check custom Vite port

param(
    [int]$LaravelPort = 8000,
    [int]$VitePort = 5173,
    [switch]$Detailed = $false
)

# Colors for output
$ColorSuccess = "Green"
$ColorWarning = "Yellow" 
$ColorError = "Red"
$ColorInfo = "Cyan"
$ColorStep = "Magenta"

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

function Test-DevServer {
    param(
        [int]$Port,
        [string]$ServerName
    )
    
    try {
        # Use Test-NetConnection to check if port is in use
        $connection = Test-NetConnection -ComputerName "localhost" -Port $Port -WarningAction SilentlyContinue -InformationLevel Quiet -ErrorAction SilentlyContinue
        
        if ($connection) {
            # Get process information for the port
            $processes = Get-NetTCPConnection -LocalPort $Port -ErrorAction SilentlyContinue | 
                        Select-Object -ExpandProperty OwningProcess -Unique
            
            if ($processes) {
                foreach ($processId in $processes) {
                    $process = Get-Process -Id $processId -ErrorAction SilentlyContinue
                    if ($process) {
                        Write-Success "$ServerName server is running on port $Port (PID: $processId, Process: $($process.Name))"
                        
                        if ($Detailed) {
                            Write-Info "  Command: $($process.ProcessName)"
                            Write-Info "  Start Time: $($process.StartTime)"
                            Write-Info "  CPU Time: $($process.TotalProcessorTime)"
                            Write-Info "  Memory: $([math]::Round($process.WorkingSet64 / 1MB, 2)) MB"
                        }
                        
                        return $true
                    }
                }
            }
            
            Write-Warning "$ServerName port $Port is in use but process details unavailable"
            return $true
        } else {
            Write-DevError "$ServerName server is NOT running on port $Port"
            return $false
        }
    } catch {
        Write-DevError "Failed to check $ServerName server on port $Port`: $($_.Exception.Message)"
        return $false
    }
}

function Test-ServerHealth {
    param(
        [int]$Port,
        [string]$ServerName,
        [string]$HealthEndpoint = "/"
    )
    
    try {
        $uri = "http://127.0.0.1:$Port$HealthEndpoint"
        $response = Invoke-WebRequest -Uri $uri -Method GET -TimeoutSec 5 -ErrorAction Stop
        
        if ($response.StatusCode -eq 200) {
            Write-Success "$ServerName health check passed (HTTP $($response.StatusCode))"
            return $true
        } else {
            Write-Warning "$ServerName responded with HTTP $($response.StatusCode)"
            return $false
        }
    } catch {
        Write-DevError "$ServerName health check failed: $($_.Exception.Message)"
        return $false
    }
}

# Main execution
try {
    Write-Host "🔍 Checking Development Environment Status" -ForegroundColor $ColorStep
    Write-Host "=========================================" -ForegroundColor $ColorStep
    Write-Host ""
    
    $laravelRunning = Test-DevServer -Port $LaravelPort -ServerName "Laravel"
    $viteRunning = Test-DevServer -Port $VitePort -ServerName "Vite"
    
    Write-Host ""
    
    # Health checks if servers are running
    if ($laravelRunning) {
        Test-ServerHealth -Port $LaravelPort -ServerName "Laravel" -HealthEndpoint "/"
    }
    
    if ($viteRunning) {
        Test-ServerHealth -Port $VitePort -ServerName "Vite" -HealthEndpoint "/"
    }
    
    Write-Host ""
    
    # Summary
    if ($laravelRunning -and $viteRunning) {
        Write-Success "Development environment is fully operational!"
        Write-Info "🚀 Main App: http://127.0.0.1:$LaravelPort"
        Write-Info "📡 API: http://127.0.0.1:$LaravelPort/api"
        Write-Info "⚡ Vite HMR: http://127.0.0.1:$VitePort"
        exit 0
    } elseif ($laravelRunning -or $viteRunning) {
        Write-Warning "Development environment is partially running"
        Write-Info "Use 'composer dev-start' to start all servers"
        exit 1
    } else {
        Write-DevError "Development environment is NOT running"
        Write-Info "Use 'composer dev-start' to start the development environment"
        exit 2
    }
    
} catch {
    Write-DevError "Error checking development environment: $($_.Exception.Message)"
    exit 3
}
