# Inventory App - Deployment Architecture

## Overview

The Inventory Management API uses a **two-stage deployment architecture**:

1. **Release Creation** (`release-deployment.yml`) - Runs on GitHub infrastructure (Ubuntu)
   - Builds and creates versioned release artifacts
   - Triggered automatically on new GitHub release tags

2. **Continuous Deployment** (`continuous-deployment.yml`) - Runs on on-premises runner (Windows)
   - Downloads and deploys the release artifact to production
   - Triggered automatically when release is published
   - Can be manually triggered for rollback or retry

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│ Developer                                                           │
│ • git tag v1.0.0 && git push --tags                               │
└──────────────────────┬──────────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────────────┐
│ GitHub Release Created (Automatic)                                  │
│ • Creates ZIP artifact with built app                              │
└──────────────────────┬──────────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────────────┐
│ Continuous Deployment Workflow                                      │
│ Runner: self-hosted Windows machine (on-premises)                   │
│                                                                     │
│ 1. Download ZIP from GitHub release                                │
│ 2. Extract to temp directory                                       │
│ 3. Run Deploy-Application.ps1                                      │
│    ├─ Test system prerequisites                                    │
│    ├─ Create staging-YYYYMMDD-HHMMSS                              │
│    ├─ Setup shared storage symlinks                                │
│    ├─ Swap production symlink (atomic)                             │
│    ├─ Generate .env file                                           │
│    ├─ Run migrations & setup                                       │
│    └─ Bring app online                                             │
│ 4. Verify deployment health                                        │
│ 5. Upload logs as artifact                                         │
└─────────────────────────────────────────────────────────────────────┘
```

## File Structure

```
.github/
├── workflows/
│   ├── release-deployment.yml          # Build & create artifact
│   └── continuous-deployment.yml       # Deploy to production
└── instructions/
    └── ps1.instructions.md             # PowerShell guidelines

scripts/
├── Deploy-Application.ps1              # Main deployment entry point
└── InventoryApp.Deployment/
    ├── InventoryApp.Deployment.psm1   # PowerShell module
    └── InventoryApp.Deployment.psd1   # Module manifest
```

## Deployment Workflow

### 1. Release Creation (`release-deployment.yml`)

**Trigger**: `on: release: types: [published]`

Steps:

1. Checkout code
2. Setup Node.js and PHP
3. Install dependencies (Composer + npm)
4. Build SPA and CSS
5. Clean temporary files
6. Create ZIP artifact
7. Upload to GitHub release

**Result**: `inventory-app-release-<run-id>.zip` uploaded to release assets

### 2. Continuous Deployment (`continuous-deployment.yml`)

**Triggers**:

- **Automatic**: When a release is published
- **Manual**: `workflow_dispatch` with tag and environment selection

**Run Environment**: Self-hosted Windows runner with labels `[self-hosted, windows, production]`

**Steps**:

1. **Checkout Repository**
   - Get latest code to access deployment scripts

2. **Determine Release Information**
   - Parse tag from release event or manual input
   - Validate tag exists

3. **Get Release Asset URL**
   - Query GitHub API for ZIP download URL
   - Validate asset exists

4. **Create Deployment Log**
   - Create temp directory for logs
   - Initialize deployment log file

5. **Download Release Asset**
   - Download ZIP from GitHub API
   - Log file size and download time

6. **Extract Release Package**
   - Extract ZIP to temp directory
   - Find Laravel app directory
   - Validate single app root exists

7. **Validate Deployment Package**
   - Check required files: `artisan`, `composer.json`, `.env.example`
   - Check required directories: `app/`, `database/`, `routes/`, etc.
   - Fail if package is corrupted

8. **Deploy Application**
   - Run `Deploy-Application.ps1` with credentials from GitHub secrets
   - Passes database password as SecureString (not logged)
   - Logs all output to file
   - Returns exit code 0 on success

9. **Verify Deployment Health**
   - Check health endpoint for HTTP 200
   - Retry up to 5 times with 5-second delays
   - Log warnings if endpoint not responding

10. **Upload Deployment Logs**
    - Save logs as GitHub Actions artifact
    - Retain for 30 days

11. **Report Deployment Status**
    - Log success/failure message
    - Exit with appropriate code

## GitHub Secrets Configuration

Configure these secrets in GitHub repository settings under **Settings → Secrets and variables → Actions**:

| Secret                   | Description                      | Example                                    |
| ------------------------ | -------------------------------- | ------------------------------------------ |
| `APP_KEY`                | Laravel APP_KEY (base64 encoded) | `base64:xxxxx...`                          |
| `APP_URL`                | Application URL                  | `https://inventory.museumwnf.org`          |
| `APP_NAME`               | Display name                     | `Inventory App`                            |
| `DATABASE_HOST`          | Database hostname/IP             | `127.0.0.1` or `db.example.com`            |
| `DATABASE_PORT`          | Database port                    | `3306`                                     |
| `DATABASE_NAME`          | Database name                    | `inventory_db`                             |
| `DATABASE_USERNAME`      | Database user                    | `app`                                      |
| `DATABASE_PASSWORD`      | Database password                | `secure_password`                          |
| `WEBSERVER_PATH_PROD`    | Production webserver path        | `C:\Apache24\htdocs\inventory-app`         |
| `WEBSERVER_PATH_STAGING` | Staging webserver path           | `C:\Apache24\htdocs\inventory-app-staging` |
| `SHARED_STORAGE_ROOT`    | Shared storage root path         | `C:\mwnf-server\github-apps`               |
| `PHP_PATH`               | PHP executable path              | `C:\php\php.exe`                           |

**Note**: GitHub tokens are provided automatically via `${{ secrets.GITHUB_TOKEN }}` and `${{ secrets.GITHUB_TOKEN }}` for package access.

## Manual Deployment

### Using `workflow_dispatch`

1. Go to **GitHub → Actions → Continuous Deployment**
2. Click **Run workflow**
3. Select:
   - **Release tag**: Version to deploy (e.g., `v1.0.0`)
   - **Environment**: `production` or `staging`
4. Click **Run workflow**

The workflow will download and deploy the specified release.

## Deploy-Application.ps1 Reference

The deployment script is a wrapper that loads the PowerShell module and executes the deployment.

### Usage

```powershell
$dbPassword = ConvertTo-SecureString 'your-db-password' -AsPlainText -Force
$databaseCredential = New-Object System.Management.Automation.PSCredential ('app', $dbPassword)

.\scripts\Deploy-Application.ps1 `
  -DeploymentPackagePath 'C:\temp\inventory-app-release' `
  -WebserverPath 'C:\Apache24\htdocs\inventory-app' `
  -SharedStorageRoot 'C:\mwnf-server\github-apps' `
  -PhpPath 'C:\php\php.exe' `
  -AppUrl 'https://inventory.museumwnf.org' `
  -AppName 'Inventory App' `
  -AppEnv 'production' `
  -AppKey 'base64:xxxxx...' `
  -DatabaseHost '127.0.0.1' `
  -DatabasePort 3306 `
  -DatabaseName 'inventory_db' `
  -DatabaseCredential $databaseCredential `
  -Verbose
```

### Exit Codes

- **0** - Deployment successful
- **1** - Deployment failed (check logs)
- **2** - System prerequisites not met

## InventoryApp.Deployment Module

PowerShell module providing deployment functions:

### Public Functions

#### `Deploy-Application`

Main deployment orchestration function

- Creates staging directory
- Sets up shared storage symlinks
- Swaps production symlink (atomic)
- Runs migrations and setup
- Handles error rollback

#### `Test-SystemPrerequisites`

Validates system prerequisites

- PHP 8.2+ installed
- Laravel app structure exists
- Adequate disk space
- Write permissions verified

#### `Test-DeploymentPackage`

Validates package integrity

- Required files present
- Directory structure intact
- No corruption detected

#### `New-StagingDirectory`

Creates timestamped staging directory

- Pattern: `staging-YYYYMMDD-HHMMSS`
- Returns path for deployment

#### `Invoke-LaravelSetup`

Runs Laravel initialization

- Database migrations
- Permission sync
- Configuration caching
- Route/view caching

#### `Swap-WebserverSymlink`

Atomic production swap

- Backs up current → `production_swap`
- Activates new staging → `production`
- Rollback capability on failure

See `scripts/InventoryApp.Deployment/InventoryApp.Deployment.psm1` for detailed documentation.

## Storage Architecture

All deployments use the same shared storage structure:

```
C:\mwnf-server\github-apps\
├── production                  (symlink to latest staging-YYYYMMDD-HHMMSS)
│   ├── app/
│   ├── routes/
│   ├── resources/
│   ├── storage/               (symlink to ../shared-storage/storage)
│   └── ... (app files)
│
├── staging-20251031-041516    (current deployment)
├── staging-20251030-123456    (previous - can rollback)
├── staging-20251029-085743    (older)
│
└── shared-storage/
    └── storage/               (persistent across deployments)
       ├── app/
       ├── logs/
       ├── framework/
       │  ├── cache/
       │  ├── sessions/
       │  └── views/
       └── custom/
```

**Benefits**:

- Logs, cache, sessions preserved across deployments
- Easy rollback via symlink swap
- Isolated deployment environments
- Shared configuration and uploads

## Troubleshooting

### Check Deployment Logs

1. Go to **GitHub → Actions → Continuous Deployment**
2. Click the failed workflow run
3. Expand failed step and review output
4. Download **deployment-logs** artifact for full logs

### Manual Rollback

If deployment fails and you need to rollback:

1. Identify the previous staging directory: `staging-YYYYMMDD-HHMMSS`
2. Run manually via `workflow_dispatch` with a previous release tag
3. Or use PowerShell directly:

```powershell
cmd /c mklink /D C:\mwnf-server\github-apps\production C:\mwnf-server\github-apps\staging-20251030-123456
```

### Common Issues

**Issue**: Deployment fails with "PHP not found"

- **Solution**: Verify `PHP_PATH` secret is set correctly and PHP is installed

**Issue**: Database migration fails

- **Solution**: Check database credentials in secrets, verify database is running and accessible

**Issue**: Symlink swap fails with "permission denied"

- **Solution**: Ensure GitHub Actions runner has Administrator privileges

**Issue**: Health check fails but app is working

- **Solution**: Verify `APP_URL` secret is correct and app is accessible from runner

## Local Testing

To test deployment locally without GitHub Actions:

### 1. Download a Release

```powershell
# Get the latest release ZIP
$owner = 'metanull'
$repo = 'inventory-app'
$release = Invoke-RestMethod "https://api.github.com/repos/$owner/$repo/releases/latest"
$zipUrl = $release.assets[0].browser_download_url
Invoke-WebRequest -Uri $zipUrl -OutFile 'C:\temp\inventory-app.zip'
```

### 2. Extract and Deploy

```powershell
# Extract
Expand-Archive -Path 'C:\temp\inventory-app.zip' -DestinationPath 'C:\temp\release'

# Deploy
$dbPassword = ConvertTo-SecureString 'password' -AsPlainText -Force
$credential = New-Object System.Management.Automation.PSCredential ('app', $dbPassword)

.\scripts\Deploy-Application.ps1 `
  -DeploymentPackagePath 'C:\temp\release\inventory-app' `
  -WebserverPath 'C:\Apache24\htdocs\inventory-app' `
  -SharedStorageRoot 'C:\mwnf-server\github-apps' `
  -PhpPath 'C:\php\php.exe' `
  -AppUrl 'https://inventory.local' `
  -AppName 'Inventory App' `
  -AppEnv 'production' `
  -AppKey 'base64:xxxxx...' `
  -DatabaseHost '127.0.0.1' `
  -DatabasePort 3306 `
  -DatabaseName 'inventory_db' `
  -DatabaseCredential $credential `
  -Verbose
```

### 3. Verify

```powershell
# Check production symlink points to new staging
Get-Item -Path 'C:\mwnf-server\github-apps\production' | Select-Object Target, LinkType

# Check storage symlink
Get-Item -Path 'C:\Apache24\htdocs\inventory-app\storage' | Select-Object Target, LinkType

# Test app
Invoke-WebRequest 'https://inventory.local/api/health' -SkipCertificateCheck
```

## Security Considerations

1. **Database Password**: Passed as `SecureString`, never logged to console
2. **APP_KEY**: Stored in GitHub secrets, not visible in workflow output
3. **GitHub Token**: Automatically masked by GitHub Actions
4. **Environment Isolation**: Staging and production use separate credentials (configurable)
5. **Artifact Cleanup**: Logs and temp files removed after workflow completes

## Related Documentation

- [PowerShell Module](./scripts/InventoryApp.Deployment/README.md) - Detailed function documentation
- [Deployment Script Guide](./scripts/Deploy-Application.ps1) - Script parameter reference
- [GitHub Actions Documentation](https://docs.github.com/en/actions)
