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

- `Start-DevelopmentServer` - Start the full Laravel development environment (web, vite, queue)
  - `Start-WorkerWeb` - Start the Laravel web server
  - `Start-WorkerVite` - Start the Vite development server
  - `Start-WorkerQueue` - Start Laravel queue worker(s)
- `Stop-DevelopmentServer` - Stop the full Laravel development environment
  - `Stop-WorkerWeb` - Stop the Laravel web server
  - `Stop-WorkerVite` - Stop the Vite development server
  - `Stop-WorkerQueue` - Stop Laravel queue worker(s)
- `Test-DevelopmentServer` - Test the status of the full Laravel development environment
  - `Test-WorkerWeb` - Test the Laravel web server status
  - `Test-WorkerVite` - Test the Vite development server status
  - `Test-WorkerWeb` - Test the Laravel web server status
- `Invoke-Linter` - Run linter on the Laravel application
- `Invoke-Test` - Run tests on the Laravel application