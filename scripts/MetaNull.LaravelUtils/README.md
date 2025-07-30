# MetaNull.LaravelUtils Module

## Module Relocated

The `MetaNull.LaravelUtils` PowerShell module has been moved to a separate repository for better modularity and reusability across projects.

## New Location

The module is now available at:
**Repository:** https://github.com/metanull/ManageMyOwnWebServerOnWindows  
**Module Path:** `/source/` directory within the repository

## Installation

To use the module in this project, you can install it using PowerShell:

```powershell
# Install from PowerShell Gallery (if published)
Install-Module -Name MetaNull.LaravelUtils -Scope CurrentUser

# Or clone the repository and import manually
git clone https://github.com/metanull/ManageMyOwnWebServerOnWindows.git
Import-Module ".\ManageMyOwnWebServerOnWindows\source\MetaNull.LaravelUtils.psd1"
```

## Available Functions

The module provides the following Laravel development functions:

- `Start-Laravel` - Start Laravel development server
- `Stop-Laravel` - Stop Laravel development server  
- `Test-Laravel` - Test Laravel server status
- `Start-LaravelVite` - Start Vite development server
- `Stop-LaravelVite` - Stop Vite development server
- `Test-LaravelVite` - Test Vite server status
- `Start-LaravelQueue` - Start Laravel queue worker
- `Stop-LaravelQueue` - Stop Laravel queue worker
- `Test-LaravelQueue` - Test Laravel queue status
- `Start-LaravelWeb` - Start web server
- `Stop-LaravelWeb` - Stop web server
- `Test-LaravelWeb` - Test web server status

## Usage in This Project

This project now uses simplified wrapper scripts that automatically load the module:

- `Start-DevServer.ps1` - Starts Laravel development environment
- `Stop-DevServer.ps1` - Stops Laravel development environment  
- `Test-DevServer.ps1` - Tests Laravel development environment status

These scripts will automatically offer to install the module if it's not already available.
