---
description: "Use when: configuring the MWNF Windows production server, writing or modifying the deploy.yml workflow, editing Deploy-Application.ps1 or the InventoryApp.Deployment module, managing Apache vhosts for inventory.museumwnf.org, understanding the blue-green symlink deployment, connecting to virtual-office.museumwnf.org via PSSession, or inspecting the self-hosted GitHub Actions runner."
applyTo: "scripts/Deploy-Application.ps1,scripts/InventoryApp.Deployment/**,.github/workflows/deploy.yml"
---

# Server Setup — MWNF Windows Server (Primary Deployment)

The inventory-app's **primary** deployment target is the Museum With No Frontiers Windows server at `virtual-office.museumwnf.org`. A secondary deployment to an OVH VPS is documented in `server-setup-ovh.instructions.md`.

## Topology

```
┌─────────────────────────────────────────────────────────────┐
│  virtual-office.museumwnf.org (Windows Server)              │
│  VPN-only access                                            │
│                                                             │
│  Apache (XAMPP) · MariaDB · PHP 8.x                         │
│  Self-hosted GitHub Actions runner (SVR-MWNF)               │
│                                                             │
│  C:\mwnf-server\                                            │
│  ├── software\current\ → versioned installs (Apache, PHP)   │
│  ├── apps\             → legacy apps (webhooks, etc.)        │
│  ├── github-apps\      → inventory-app deployments           │
│  │   ├── production\   → blue-green releases + symlink       │
│  │   │   ├── inventory-app → staging-YYYYMMDD-HHmmss (sym)  │
│  │   │   └── shared-storage\                                 │
│  │   └── temp\         → full repo clone (scripts, importer) │
│  ├── configuration\current\mwnf\ → vhost symlinks           │
│  ├── reverse-proxy\    → Apache reverse proxy + mod_security │
│  ├── pictures\         → museum images + cache               │
│  └── ssl\              → TLS certificates                    │
└─────────────────────────────────────────────────────────────┘
```

## Remote Access

The server is reachable only through a dedicated VPN connection. When connected:

```powershell
# Connect via PSSession (PowerShell 5+)
$session = New-PSSession -ComputerName virtual-office.museumwnf.org

# Run commands remotely
Invoke-Command -Session $session { Get-ChildItem C:\mwnf-server\github-apps\production }

# Close when done
Remove-PSSession $session
```

The `server-inspector` agent provides read-only inspection capabilities via this PSSession pattern.

## Two Instances on the Server

| Instance | Path | Purpose |
|----------|------|---------|
| **Production** | `C:\mwnf-server\github-apps\production\inventory-app` (symlink) | Deployed by CI/CD, serves the web app |
| **Temp** | `C:\mwnf-server\github-apps\temp\inventory-app` | Full repo clone for running scripts/importer |

**Warning:** The temp instance shares the same database as production. Destructive commands (`db:wipe`, `migrate:refresh`) affect live data.

## Blue-Green Deployment (Symlink Swap)

The `deploy.yml` workflow uses atomic symlink swapping for zero-downtime deployment:

```
C:\mwnf-server\github-apps\production\
├── inventory-app          → staging-20260415-143022  (current symlink)
├── staging-20260415-143022\  (current release)
├── staging-20260410-091500\  (previous release)
├── staging-20260405-120000\  (oldest kept)
└── shared-storage\           (persists across deployments)
    └── app\
        ├── private\
        ├── public\
        └── public\images\
```

The swap sequence:
1. Extract release into `staging-<timestamp>/`
2. Symlink `staging-<timestamp>/storage/app` → `shared-storage/app`
3. Rename current `inventory-app` symlink to `inventory-app_swap` (backup)
4. Rename new temp symlink to `inventory-app` (atomic swap)
5. On failure: roll back by renaming `inventory-app_swap` back
6. Cleanup: remove `_swap` symlink, prune old staging dirs (keep last 3)

## Deployment Flow

```
Manual trigger (workflow_dispatch) with release tag
    │
    ▼
deploy.yml — runs on [self-hosted, windows] (SVR-MWNF runner)
    │
    ├── download job
    │   Download release asset (inventory-app.zip) from GitHub Release
    │   Extract to runner temp, copy to staging-<timestamp>
    │
    ├── down job
    │   php artisan down --retry=120
    │
    ├── deploy job
    │   Setup shared storage symlink (storage/app → shared-storage/app)
    │   Atomic symlink swap (blue-green)
    │
    ├── configure job
    │   Generate .env from .env.example + GitHub Environment variables
    │   php artisan migrate --force
    │   php artisan permissions:sync --production
    │   php artisan config:cache, route:cache, view:cache
    │
    ├── up job
    │   php artisan up
    │
    └── intendance job
        Cleanup swap symlinks
        Prune old staging dirs (keep last 3)
```

## GitHub Actions Runner

| Property | Value |
|----------|-------|
| Install path | `C:\mwnf-server\software\github\actions-runner` |
| Windows service | `actions.runner.metanull-inventory-app.SVR-MWNF` |
| Agent name | `SVR-MWNF` |
| Runs as | `NT AUTHORITY\NETWORK SERVICE` |
| Scope | Repo-level self-hosted runner |

## GitHub Environment: MWNF-SVR

All deployment configuration is stored in the **`MWNF-SVR`** GitHub Environment (Settings → Environments).

### Variables

| Variable | Default | Purpose |
|----------|---------|---------|
| `PHP_PATH` | `C:\Program Files\PHP\php.exe` | Path to PHP executable |
| `WEBSERVER_PATH` | `C:\Apache24\htdocs\inventory-app` | Production symlink path |
| `APP_NAME` | `inventory-app` | Application display name |
| `APP_ENV` | `production` | Laravel environment |
| `APP_DEBUG` | `false` | Debug mode |
| `APP_URL` | `http://localhost` | Application URL |
| `DB_CONNECTION` | `mysql` | Database driver |
| `DB_HOST` | `127.0.0.1` | Database host |
| `DB_PORT` | `3306` | Database port |
| `APACHE_SERVICE_USER` | `SYSTEM` | Apache service user |

### Secrets

| Secret | Purpose |
|--------|---------|
| `APP_KEY` | Laravel encryption key |
| `MARIADB_DATABASE` | Database name |
| `MARIADB_USER` | Database username |
| `MARIADB_SECRET` | Database password |

## Deployment Module

The `scripts/InventoryApp.Deployment/` PowerShell module provides reusable deployment functions:

| Function | Purpose |
|----------|---------|
| `Test-SystemPrerequisites` | Verify PHP, paths, permissions |
| `Test-DeploymentPackage` | Validate extracted release |
| `New-StagingDirectory` | Create timestamped staging dir |
| `Remove-OldStagingDirectories` | Prune old releases |
| `New-StorageSymlink` | Link shared storage into release |
| `Swap-WebserverSymlink` | Atomic symlink swap |
| `Remove-SwapBackup` | Cleanup backup symlink |
| `New-EnvironmentFile` | Generate .env from template |
| `Invoke-LaravelSetup` | Run migrations, caches |
| `Invoke-LaravelDown` | Maintenance mode |
| `Deploy-Application` | Full orchestration |

## Apache Configuration

- **Backend vhost**: `C:\mwnf-server\github-apps\production\inventory.museumwnf.org.conf`
- **Vhost symlink**: `C:\mwnf-server\configuration\current\mwnf\inventory.museumwnf.org.conf`
- **DocumentRoot**: `C:/mwnf-server/github-apps/production/inventory-app/public`
- **Backend port**: 8443 (SSL), proxied from 443 by the reverse proxy
- **Reverse proxy entry**: `C:\mwnf-server\reverse-proxy\Apache24\conf\extra\httpd-vhosts.conf`

## Forbidden

- **NEVER modify Apache config, restart services, or install software in deploy scripts or workflows.** Infrastructure changes belong in manual provisioning only.
- **NEVER run destructive database commands** (`migrate:fresh`, `migrate:refresh`, `db:wipe`) in production — only `migrate --force`.
- **NEVER commit production secrets to Git.** Use GitHub Environment secrets/variables.
- **NEVER bypass the blue-green symlink pattern.** Direct file replacement in the production directory breaks rollback.
- **NEVER use the temp instance path in deploy.yml.** It is for manual script execution only.
- **NEVER expose internal server paths or credentials in logs.** Use `::add-mask::` for sensitive values.
