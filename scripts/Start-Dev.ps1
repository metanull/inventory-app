# Start-Dev.ps1 - Development Environment Startup Script
#
# This PowerShell script starts the Laravel development environment by running
# both the Laravel artisan serve and Vite development server concurrently.
#
# Usage:
#   .\scripts\Start-Dev.ps1                     # Start development servers
#   .\scripts\Start-Dev.ps1 -Reset              # Reset database and start
#   .\scripts\Start-Dev.ps1 -LaravelPort 8001   # Use custom Laravel port
#   .\scripts\Start-Dev.ps1 -VitePort 5174      # Use custom Vite port

param(
    [switch]$Reset = $false,
    [int]$LaravelPort = 8000,
    [int]$VitePort = 5173,
    [switch]$SkipBuild = $false,
    [switch]$Verbose = $false
)

# Configuration
$ErrorActionPreference = "Stop"
$OriginalLocation = Get-Location

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

function Test-Command {
    param([string]$Command)
    try {
        $null = Get-Command $Command -ErrorAction Stop
        return $true
    } catch {
        return $false
    }
}

function Test-Port {
    param([int]$Port)
    try {
        # Use Test-NetConnection which is a standard PowerShell command
        $result = Test-NetConnection -ComputerName "localhost" -Port $Port -WarningAction SilentlyContinue -InformationLevel Quiet -ErrorAction SilentlyContinue 2>$null
        # Return true if port IS in use (connection successful), false if port is available
        return $result.TcpTestSucceeded
    } catch {
        return $false
    }
}

function Stop-ProcessOnPort {
    param([int]$Port)
    try {
        $processes = Get-NetTCPConnection -LocalPort $Port -ErrorAction SilentlyContinue | 
                    Select-Object -ExpandProperty OwningProcess -Unique
        
        foreach ($processId in $processes) {
            if ($processId -and $processId -ne 0) {
                $process = Get-Process -Id $processId -ErrorAction SilentlyContinue
                if ($process) {
                    Write-Warning "Stopping process $($process.Name) (PID: $processId) on port $Port"
                    Stop-Process -Id $processId -Force
                    Start-Sleep -Seconds 1
                }
            }
        }
    } catch {
        # Ignore errors when stopping processes
    }
}

function Start-LaravelServer {
    param([int]$Port)
    
    Write-Step "Starting Laravel development server on port $Port..."
    
    # Check if port is available
    if (Test-Port -Port $Port) {
        Write-Warning "Port $Port is already in use. Attempting to free it..."
        Stop-ProcessOnPort -Port $Port
        Start-Sleep -Seconds 2
        
        if (Test-Port -Port $Port) {
            Write-DevError "Unable to free port $Port. Please check what's using it and try again."
            return $null
        }
    }
    
    # Start Laravel server
    $laravelJob = Start-Job -ScriptBlock {
        param($Port)
        Set-Location $using:OriginalLocation
        php artisan serve --port=$Port --host=127.0.0.1
    } -ArgumentList $Port
    
    # Wait a moment and check if server started
    Start-Sleep -Seconds 3
    
    if (Test-Port -Port $Port) {
        Write-Success "Laravel server running at http://127.0.0.1:$Port"
        return $laravelJob
    } else {
        Write-DevError "Failed to start Laravel server"
        if ($laravelJob) {
            Stop-Job $laravelJob -ErrorAction SilentlyContinue
            Remove-Job $laravelJob -ErrorAction SilentlyContinue
        }
        return $null
    }
}

function Start-ViteServer {
    param([int]$Port)
    
    Write-Step "Starting Vite development server on port $Port..."
    
    # Check if port is available
    if (Test-Port -Port $Port) {
        Write-Warning "Port $Port is already in use. Attempting to free it..."
        Stop-ProcessOnPort -Port $Port
        Start-Sleep -Seconds 2
        
        if (Test-Port -Port $Port) {
            Write-DevError "Unable to free port $Port. Please check what's using it and try again."
            return $null
        }
    }
    
    # Start Vite server with output redirection to prevent job termination
    $viteJob = Start-Job -ScriptBlock {
        param($Port, $LaravelPort)
        Set-Location $using:OriginalLocation
        
        # Set environment variables for Vite to ensure IPv4 binding
        $env:VITE_PORT = $Port
        $env:VITE_HOST = "127.0.0.1"
        $env:VITE_DEV_SERVER_URL = "http://127.0.0.1:$Port"
        
        # Start Vite with port specification using npx directly for proper argument handling
        npx vite --host 127.0.0.1 --port $Port --strictPort 2>&1
    } -ArgumentList $Port, $LaravelPort
    
    # Wait longer for Vite to start (it takes more time)
    Start-Sleep -Seconds 5
    
    if (Test-Port -Port $Port) {
        Write-Success "Vite server running at http://127.0.0.1:$Port"
        Write-Info "Note: Access your Vue app via Laravel at http://127.0.0.1:$LaravelPort/"
        return $viteJob
    } else {
        Write-DevError "Failed to start Vite server"
        if ($viteJob) {
            # Get job output for debugging
            Start-Sleep -Seconds 2  # Give job time to produce output
            $jobOutput = Receive-Job $viteJob -ErrorAction SilentlyContinue
            if ($jobOutput) {
                Write-DevError "Vite job output:"
                Write-Host $jobOutput -ForegroundColor Red
            }
            
            # Also check job state and errors
            if ($viteJob.State -eq "Failed") {
                $jobErrors = $viteJob.ChildJobs[0].Error
                if ($jobErrors) {
                    Write-DevError "Vite job errors:"
                    foreach ($err in $jobErrors) {
                        Write-Host $err -ForegroundColor Red
                    }
                }
            }
            
            Stop-Job $viteJob -ErrorAction SilentlyContinue
            Remove-Job $viteJob -ErrorAction SilentlyContinue
        }
        return $null
    }
}

function Reset-Database {
    Write-Step "Resetting database..."
    
    try {
        # Drop all tables and re-run migrations
        php artisan migrate:fresh --force
        Write-Success "Database tables reset"
        
        # Run seeders
        php artisan db:seed --force
        Write-Success "Database seeded with test data"
        
    } catch {
        Write-DevError "Failed to reset database: $($_.Exception.Message)"
        throw
    }
}

function Test-Prerequisites {
    Write-Step "Checking prerequisites..."
    
    $errors = @()
    
    # Check PHP
    if (-not (Test-Command "php")) {
        $errors += "PHP is not installed or not in PATH"
    } else {
        $phpVersion = php -r "echo PHP_VERSION;"
        Write-Info "Found PHP version: $phpVersion"
    }
    
    # Check Composer
    if (-not (Test-Command "composer")) {
        $errors += "Composer is not installed or not in PATH"
    }
    
    # Check Node.js
    if (-not (Test-Command "node")) {
        $errors += "Node.js is not installed or not in PATH"
    } else {
        $nodeVersion = node --version
        Write-Info "Found Node.js version: $nodeVersion"
    }
    
    # Check NPM
    if (-not (Test-Command "npm")) {
        $errors += "NPM is not installed or not in PATH"
    }
    
    # Check if we're in the right directory
    if (-not (Test-Path "artisan")) {
        $errors += "Not in Laravel project directory (artisan file not found)"
    }
    
    if (-not (Test-Path "package.json")) {
        $errors += "Not in project directory (package.json not found)"
    }
    
    # Check port availability
    if (Test-Port -Port $LaravelPort) {
        Write-Warning "Laravel port $LaravelPort is already in use"
        Write-Info "Will attempt to free port $LaravelPort during startup"
    } else {
        Write-Info "Laravel port $LaravelPort is available"
    }
    
    if (Test-Port -Port $VitePort) {
        Write-Warning "Vite port $VitePort is already in use"
        Write-Info "Will attempt to free port $VitePort during startup"
    } else {
        Write-Info "Vite port $VitePort is available"
    }
    
    if ($errors.Count -gt 0) {
        Write-DevError "Prerequisites check failed:"
        foreach ($errorMsg in $errors) {
            Write-Host "  - $errorMsg" -ForegroundColor $ColorError
        }
        throw "Prerequisites not met"
    }
    
    Write-Success "Prerequisites check passed"
}

function Initialize-Environment {
    Write-Step "Initializing development environment..."
    
    # Check if .env exists
    if (-not (Test-Path ".env")) {
        if (Test-Path ".env.example") {
            Copy-Item ".env.example" ".env"
            Write-Info "Copied .env.example to .env"
            
            # Generate application key
            php artisan key:generate
            Write-Success "Generated application key"
        } else {
            Write-Warning ".env.example not found. Please create .env manually."
        }
    }
    
    # Check if storage link exists
    if (-not (Test-Path "public\storage")) {
        php artisan storage:link
        Write-Success "Created storage symbolic link"
    }
    
    # Check database file for SQLite
    $dbConnection = php artisan tinker --execute="echo config('database.default');"
    if ($dbConnection -eq "sqlite") {
        $dbPath = php artisan tinker --execute="echo config('database.connections.sqlite.database');"
        if (-not (Test-Path $dbPath)) {
            New-Item -ItemType File -Path $dbPath -Force
            Write-Success "Created SQLite database file"
        }
    }
}

function Show-Status {
    param(
        [System.Management.Automation.Job]$LaravelJob,
        [System.Management.Automation.Job]$ViteJob,
        [int]$LaravelPort,
        [int]$VitePort
    )
    
    Write-Host ""
    Write-Host "🎉 Development environment started successfully!" -ForegroundColor $ColorSuccess
    Write-Host ""
    Write-Host "📱 Application URLs:" -ForegroundColor $ColorInfo
    Write-Host "   🚀 Main App:      http://127.0.0.1:$LaravelPort" -ForegroundColor White
    Write-Host "      (Laravel serves your Vue.js frontend with HMR)" -ForegroundColor Gray
    Write-Host "   📡 Laravel API:   http://127.0.0.1:$LaravelPort/api" -ForegroundColor White
    Write-Host "   ⚡ Vite HMR:      http://127.0.0.1:$VitePort" -ForegroundColor White
    Write-Host "      (Development asset server - don't access directly)" -ForegroundColor Gray
    Write-Host ""
    Write-Host "🔧 Useful URLs:" -ForegroundColor $ColorInfo
    Write-Host "   📚 API Docs:      http://127.0.0.1:$LaravelPort/docs" -ForegroundColor White
    Write-Host "   💚 API Health:    http://127.0.0.1:$LaravelPort/api/health" -ForegroundColor White
    Write-Host "   🔑 Dashboard:     http://127.0.0.1:$LaravelPort/web/dashboard" -ForegroundColor White
    Write-Host ""
    Write-Host "💾 Development commands:" -ForegroundColor $ColorInfo
    Write-Host "   composer test          # Run PHP tests" -ForegroundColor White
    Write-Host "   npm run test           # Run Vue.js tests" -ForegroundColor White
    Write-Host "   composer ci-lint       # Check code style" -ForegroundColor White
    Write-Host "   php artisan migrate    # Run migrations" -ForegroundColor White
    Write-Host "   php artisan db:seed    # Seed database" -ForegroundColor White
    Write-Host ""
    Write-Host "⚠️  IMPORTANT: Access your Vue.js app at http://127.0.0.1:$LaravelPort (not the Vite port)" -ForegroundColor $ColorWarning
    Write-Host "Press Ctrl+C to stop all servers" -ForegroundColor $ColorWarning
}

function Cleanup {
    param(
        [System.Management.Automation.Job]$LaravelJob,
        [System.Management.Automation.Job]$ViteJob
    )
    
    Write-Host ""
    Write-Step "Stopping development servers..."
    
    if ($LaravelJob) {
        Stop-Job $LaravelJob -ErrorAction SilentlyContinue
        Remove-Job $LaravelJob -ErrorAction SilentlyContinue
        Write-Info "Laravel server stopped"
    }
    
    if ($ViteJob) {
        Stop-Job $ViteJob -ErrorAction SilentlyContinue
        Remove-Job $ViteJob -ErrorAction SilentlyContinue
        Write-Info "Vite server stopped"
    }
    
    # Clean up any remaining processes
    Stop-ProcessOnPort -Port $LaravelPort
    Stop-ProcessOnPort -Port $VitePort
    
    Write-Success "Development environment stopped"
}

# Main execution
try {
    Write-Host "🚀 Starting Inventory Management Development Environment" -ForegroundColor $ColorStep
    Write-Host "=======================================================" -ForegroundColor $ColorStep
    Write-Host ""
    
    # Test prerequisites
    Test-Prerequisites
    
    # Initialize environment
    Initialize-Environment
    
    # Reset database if requested
    if ($Reset) {
        Reset-Database
    }
    
    # Install/update dependencies if needed
    if (-not $SkipBuild) {
        if (-not (Test-Path "vendor\autoload.php")) {
            Write-Step "Installing PHP dependencies..."
            composer install
            Write-Success "PHP dependencies installed"
        }
        
        if (-not (Test-Path "node_modules")) {
            Write-Step "Installing Node.js dependencies..."
            npm install
            Write-Success "Node.js dependencies installed"
        }
    }
    
    # Start servers
    $laravelJob = Start-LaravelServer -Port $LaravelPort
    if (-not $laravelJob) {
        throw "Failed to start Laravel server"
    }
    
    $viteJob = Start-ViteServer -Port $VitePort
    if (-not $viteJob) {
        throw "Failed to start Vite server"
    }
    
    # Show status
    Show-Status -LaravelJob $laravelJob -ViteJob $viteJob -LaravelPort $LaravelPort -VitePort $VitePort
    
    Write-Host ""
    Write-Success "Development servers started successfully!"
    Write-Info "Use 'composer test-dev' to check server status"
    Write-Info "Use 'composer stop-dev' to stop the servers"
    Write-Info "Use 'composer monitor-dev' to continuously monitor the servers"
    Write-Host ""
    Write-Host "Press Ctrl+C in this window to stop all servers, or close this window." -ForegroundColor $ColorWarning
    
    # Register cleanup handler for when this process exits
    Register-EngineEvent PowerShell.Exiting -Action {
        Cleanup -LaravelJob $laravelJob -ViteJob $viteJob
    } | Out-Null
    
    # Simple wait - let the jobs run until user stops them
    try {
        # Just wait for user input or process termination
        Wait-Event -Timeout ([int]::MaxValue)
    } catch [System.Management.Automation.PipelineStoppedException] {
        # Ctrl+C was pressed - this is expected
        Write-Host ""
        Write-Step "Shutdown requested..."
    } catch {
        Write-DevError "Unexpected error: $($_.Exception.Message)"
    }
    
} catch {
    Write-DevError "Error: $($_.Exception.Message)"
    if ($Verbose) {
        Write-Host $_.Exception.StackTrace -ForegroundColor $ColorError
    }
    exit 1
} finally {
    # Cleanup
    if ($laravelJob) {
        Cleanup -LaravelJob $laravelJob -ViteJob $viteJob
    }
    
    Set-Location $OriginalLocation
}
