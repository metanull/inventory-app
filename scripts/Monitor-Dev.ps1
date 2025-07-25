# Monitor-Dev.ps1 - Continuous Development Environment Monitoring
#
# This PowerShell script continuously monitors the Laravel and Vite development servers
# and displays their status in real-time
#
# Usage:
#   .\scripts\Monitor-Dev.ps1                       # Monitor with default settings
#   .\scripts\Monitor-Dev.ps1 -Interval 10          # Check every 10 seconds
#   .\scripts\Monitor-Dev.ps1 -LaravelPort 8001     # Monitor custom Laravel port
#   .\scripts\Monitor-Dev.ps1 -VitePort 5174        # Monitor custom Vite port

param(
    [int]$Interval = 5,        # Check interval in seconds
    [int]$LaravelPort = 8000,
    [int]$VitePort = 5173,
    [switch]$Compact = $false  # Compact output mode
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

function Get-ServerStatus {
    param([int]$LaravelPort, [int]$VitePort)
    
    # Run Test-Dev script and capture output
    $testResult = & pwsh -File "scripts/Test-Dev.ps1" -LaravelPort $LaravelPort -VitePort $VitePort
    $exitCode = $LASTEXITCODE
    
    # Parse the results
    $status = @{
        LaravelRunning = $false
        ViteRunning = $false
        LaravelUrl = "http://127.0.0.1:$LaravelPort"
        ViteUrl = "http://127.0.0.1:$VitePort"
        Timestamp = Get-Date
        ExitCode = $exitCode
    }
    
    # Simple parsing based on exit code and key phrases
    if ($exitCode -eq 0) {
        $status.LaravelRunning = $true
        $status.ViteRunning = $true
    } elseif ($exitCode -eq 1) {
        # Partial failure - check which one is running
        $outputText = $testResult -join " "
        $status.LaravelRunning = $outputText -notmatch "Laravel server is NOT running"
        $status.ViteRunning = $outputText -notmatch "Vite server is NOT running"
    } else {
        # Complete failure
        $status.LaravelRunning = $false
        $status.ViteRunning = $false
    }
    
    return $status
}

function Show-Status {
    param($Status, [bool]$Compact)
    
    $timestamp = $Status.Timestamp.ToString("HH:mm:ss")
    
    if ($Compact) {
        # Single line status
        $laravelIcon = if ($Status.LaravelRunning) { "✅" } else { "❌" }
        $viteIcon = if ($Status.ViteRunning) { "✅" } else { "❌" }
        Write-Host "[$timestamp] Laravel: $laravelIcon | Vite: $viteIcon" -NoNewline
        
        if ($Status.LaravelRunning -and $Status.ViteRunning) {
            Write-Host " | 🚀 All systems operational" -ForegroundColor $ColorSuccess
        } elseif ($Status.LaravelRunning -or $Status.ViteRunning) {
            Write-Host " | ⚠️  Partial operation" -ForegroundColor $ColorWarning
        } else {
            Write-Host " | ❌ Both servers down" -ForegroundColor $ColorError
        }
    } else {
        # Detailed status
        Clear-Host
        Write-Host "🔍 Development Environment Monitor" -ForegroundColor $ColorStep
        Write-Host "=================================" -ForegroundColor $ColorStep
        Write-Host "Last Update: $timestamp" -ForegroundColor $ColorInfo
        Write-Host ""
        
        # Laravel Status
        if ($Status.LaravelRunning) {
            Write-Success "Laravel Server: RUNNING on port $LaravelPort"
            Write-Host "   📱 Application: $($Status.LaravelUrl)" -ForegroundColor White
            Write-Host "   📡 API:         $($Status.LaravelUrl)/api" -ForegroundColor White
        } else {
            Write-DevError "Laravel Server: NOT RUNNING on port $LaravelPort"
        }
        
        Write-Host ""
        
        # Vite Status
        if ($Status.ViteRunning) {
            Write-Success "Vite Server: RUNNING on port $VitePort"
            Write-Host "   ⚡ Hot Reload:  $($Status.ViteUrl)" -ForegroundColor White
            Write-Host "   🎨 Asset Server: Active" -ForegroundColor White
        } else {
            Write-DevError "Vite Server: NOT RUNNING on port $VitePort"
        }
        
        Write-Host ""
        
        # Overall Status
        if ($Status.LaravelRunning -and $Status.ViteRunning) {
            Write-Host "🎉 " -NoNewline -ForegroundColor $ColorSuccess
            Write-Host "Development environment fully operational!" -ForegroundColor $ColorSuccess
            Write-Host "   Access your app at: $($Status.LaravelUrl)" -ForegroundColor White
        } elseif ($Status.LaravelRunning) {
            Write-Host "⚠️  " -NoNewline -ForegroundColor $ColorWarning
            Write-Host "Laravel running, but Vite is down (no hot reload)" -ForegroundColor $ColorWarning
        } elseif ($Status.ViteRunning) {
            Write-Host "⚠️  " -NoNewline -ForegroundColor $ColorWarning
            Write-Host "Vite running, but Laravel is down (app inaccessible)" -ForegroundColor $ColorWarning
        } else {
            Write-Host "❌ " -NoNewline -ForegroundColor $ColorError
            Write-Host "Development environment is down" -ForegroundColor $ColorError
            Write-Host "   Run 'composer dev-start' to start servers" -ForegroundColor White
        }
        
        Write-Host ""
        Write-Host "Press Ctrl+C to stop monitoring" -ForegroundColor $ColorInfo
        Write-Host "Next check in $Interval seconds..." -ForegroundColor Gray
    }
}

function Show-InitialInfo {
    if (-not $Compact) {
        Write-Host "🔍 Starting Development Environment Monitor" -ForegroundColor $ColorStep
        Write-Host "===========================================" -ForegroundColor $ColorStep
        Write-Host ""
        Write-Info "Monitoring Laravel on port $LaravelPort"
        Write-Info "Monitoring Vite on port $VitePort"
        Write-Info "Check interval: $Interval seconds"
        if ($Compact) {
            Write-Info "Compact mode: enabled"
        }
        Write-Host ""
        Write-Host "Press Ctrl+C to stop monitoring" -ForegroundColor $ColorWarning
        Write-Host ""
    }
}

# Main execution
try {
    Show-InitialInfo
    
    $iterationCount = 0
    while ($true) {
        try {
            # Get current status
            $status = Get-ServerStatus -LaravelPort $LaravelPort -VitePort $VitePort
            
            # Show status
            Show-Status -Status $status -Compact $Compact
            
            # Wait for next check
            Start-Sleep -Seconds $Interval
            $iterationCount++
            
        } catch {
            Write-DevError "Error checking server status: $($_.Exception.Message)"
            Start-Sleep -Seconds $Interval
        }
    }
    
} catch [System.Management.Automation.PipelineStoppedException] {
    # Ctrl+C was pressed
    Write-Host ""
    Write-Info "Monitoring stopped by user"
} catch {
    Write-DevError "Monitoring error: $($_.Exception.Message)"
    exit 1
}
