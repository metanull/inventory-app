---
layout: default
title: Deployment Quick Start
nav_order: 8.1
parent: Deployment Guide
---

# Deployment Quick Start

Quick reference for common deployment scenarios.

## One-Time Setup (First Deployment)

Run these commands once on your Windows server:

```powershell
# 1. Create required directories
New-Item -ItemType Directory -Path "C:\mwnf-server\github-apps" -Force
New-Item -ItemType Directory -Path "C:\mwnf-server\github-apps\shared-storage\storage" -Force

# 2. Generate APP_KEY (on any machine with Laravel installed)
php artisan key:generate --show
# Save the output: base64:xxxxx...

# 3. Configure Apache (or your webserver)
# Point document root to: C:\Apache24\htdocs\inventory-app
```

## Standard Production Deployment

```powershell
# 1. Download and extract release
Expand-Archive -Path "C:\temp\inventory-app-release-*.zip" -DestinationPath "C:\temp\inventory-app-release"

# 2. Run deployment
$dbPassword = ConvertTo-SecureString "your-db-password" -AsPlainText -Force

.\scripts\Deploy-Application.ps1 `
    -DeploymentPackagePath "C:\temp\inventory-app-release" `
    -WebserverPath "C:\Apache24\htdocs\inventory-app" `
    -SharedStorageRoot "C:\mwnf-server\github-apps" `
    -PhpPath "C:\php\php.exe" `
    -AppUrl "https://inventory.museumwnf.org" `
    -AppName "Inventory App" `
    -AppEnv "production" `
    -AppKey "base64:xxxxx..." `
    -DatabaseHost "127.0.0.1" `
    -DatabasePort 3306 `
    -DatabaseName "inventory_db" `
    -DatabaseUsername "app" `
    -DatabasePassword $dbPassword `
    -Verbose

# 3. Verify deployment
Start-Process "https://inventory.museumwnf.org"
```

## Verify Deployment Success

```powershell
# Check current production version
Get-Item "C:\Apache24\htdocs\inventory-app" |
    Select-Object LinkType, @{Name="Target"; Expression={$_.Target}}

# Check logs for errors
Get-ChildItem "C:\mwnf-server\github-apps\shared-storage\storage\logs\laravel-*.log" |
    Sort-Object -Property LastWriteTime -Descending |
    Select-Object -First 1 |
    Get-Content -Tail 50

# Test API endpoint
Invoke-WebRequest -Uri "https://inventory.museumwnf.org/api/health" `
    -SkipCertificateCheck |
    Select-Object -ExpandProperty StatusCode
```

## Quick Rollback

```powershell
# List recent deployments
Get-ChildItem -Path "C:\mwnf-server\github-apps" `
    -Directory -Filter "staging-*" |
    Sort-Object -Property Name -Descending |
    Select-Object -First 3 -ExpandProperty Name

# Rollback to previous version
$previousVersion = "staging-20251030-123456"

Remove-Item "C:\Apache24\htdocs\inventory-app" -Force
New-Item -ItemType Junction `
    -Path "C:\Apache24\htdocs\inventory-app" `
    -Target "C:\mwnf-server\github-apps\$previousVersion"

# Verify rollback
Get-Item "C:\Apache24\htdocs\inventory-app" |
    Select-Object LinkType, @{Name="Target"; Expression={$_.Target}}
```

## Troubleshooting Quick Tips

```powershell
# Check PHP installation
C:\php\php.exe --version

# Test database connection
mysql -h 127.0.0.1 -u app -p inventory_db -e "SELECT 1;"

# Check disk space
Get-Volume -DriveLetter C | Select-Object SizeRemaining, Size |
    Format-Table -AutoSize

# View recent deployment logs
$logDir = "C:\mwnf-server\github-apps\shared-storage\storage\logs"
Get-ChildItem "$logDir\*.log" |
    Sort-Object -Property LastWriteTime -Descending |
    Select-Object -First 1 |
    Get-Content -Tail 100
```

## Deployment Parameters Reference

```powershell
# All required parameters for Deploy-Application.ps1

# Source and destination
-DeploymentPackagePath "C:\temp\inventory-app-release"
-WebserverPath "C:\Apache24\htdocs\inventory-app"
-SharedStorageRoot "C:\mwnf-server\github-apps"

# PHP executable
-PhpPath "C:\php\php.exe"

# Application settings
-AppUrl "https://inventory.museumwnf.org"
-AppName "Inventory App"
-AppEnv "production"
-AppKey "base64:xxxxx..."

# Database
-DatabaseHost "127.0.0.1"
-DatabasePort 3306
-DatabaseName "inventory_db"
-DatabaseUsername "app"
-DatabasePassword $secureString

# Optional
-DatabaseSslMode "prefer"           # skip-verify, prefer, require
-KeepStagingCount 3                 # Number of old deployments to keep
-Verbose                            # Show verbose output
```

## Environment Checklists

### Before Production Deployment

- [ ] PHP 8.2+ installed and verified
- [ ] Database created and credentials verified
- [ ] APP_KEY generated (`php artisan key:generate --show`)
- [ ] Shared storage directories created (`C:\mwnf-server\github-apps\shared-storage`)
- [ ] Webserver configured to serve from production symlink
- [ ] 5GB+ free disk space available
- [ ] Running PowerShell as Administrator

### After Deployment

- [ ] Application accessible at AppUrl
- [ ] No errors in Laravel logs (`shared-storage/storage/logs/`)
- [ ] Database migrations completed successfully
- [ ] API health check responds with 200 OK
- [ ] Configuration cached (`.env` in place)
- [ ] Previous deployments available for rollback
- [ ] Production symlink points to new staging version

## Next Steps

- [Full Deployment Guide](/deployment) - Comprehensive documentation
- [GitHub Releases](https://github.com/metanull/inventory-app/releases) - Download releases
- [Architecture Documentation](/deployment/#) - How it works
