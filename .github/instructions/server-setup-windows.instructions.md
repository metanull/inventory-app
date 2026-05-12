---
description: "Use when: configuring the primary Windows production server, writing or modifying deploy.yml, editing Deploy-Application.ps1 or the InventoryApp.Deployment module, managing Apache vhosts, understanding the blue-green symlink deployment, connecting via PSSession, or inspecting the self-hosted GitHub Actions runner."
applyTo: "scripts/Deploy-Application.ps1,scripts/InventoryApp.Deployment/**,.github/workflows/deploy.yml"
---

# Server Setup: Primary Windows Deployment

The inventory-app has a primary Windows deployment target. Shared instructions describe the deployment model and safety rules. Contributor-specific connection details, local aliases, concrete server paths, and private URLs live in `.copilot/local/server-windows.md`.

Before using a host, path, or private URL, read `.copilot/local/server-windows.md` first. If the file is missing or incomplete, ask the user for the missing value before executing commands or writing examples. Do not guess PSSession hosts, drive roots, inventory instance paths, log paths, or private URLs.

Use `.copilot/local/server-windows.template.md` as the collaborator-facing schema. Do not store passwords, certificates, private keys, tokens, or production `.env` contents in local config.

## Required Local Config Keys

- `pssession_computer_name`
- `vpn_required`
- `server_root`
- `apps_root`
- `dynapps_root`
- `configuration_root`
- `software_root`
- `pictures_root`
- `github_apps_root`
- `inventory_production_root`
- `inventory_temp_root`
- `inventory_laravel_log`
- `inventory_url`
- `backoffice_host`

## Topology Model

The Windows target uses:

- A server root containing versioned software, legacy apps, inventory app deployments, configuration snapshots, image storage, and TLS material.
- A primary inventory production instance deployed from CI/CD artifacts.
- A temp inventory instance that is a full repository clone for manual scripts such as the legacy importer.
- A self-hosted GitHub Actions runner used by the Windows deployment workflow.
- Apache and reverse-proxy configuration backed by symlinks.

Use local config keys rather than hard-coded paths:

| Area | Local config key |
|------|------------------|
| Server root | `server_root` |
| Legacy apps root | `apps_root` |
| Exhibition apps root | `dynapps_root` |
| Configuration root | `configuration_root` |
| Software root | `software_root` |
| Image root | `pictures_root` |
| GitHub-deployed apps root | `github_apps_root` |
| Inventory production root | `inventory_production_root` |
| Inventory temp root | `inventory_temp_root` |
| Inventory Laravel log | `inventory_laravel_log` |

## Remote Access

The server may require VPN access. Use the configured PSSession target:

```powershell
$session = New-PSSession -ComputerName '<pssession_computer_name>'
Invoke-Command -Session $session { <command> }
Remove-PSSession $session
```

The `server-inspector-windows` agent provides read-only inspection capabilities through this PSSession pattern.

## Two Inventory Instances

| Instance | Purpose |
|----------|---------|
| Production | Deployed artifact, serves the web app, follows the blue-green symlink pattern. |
| Temp | Full repository clone for manual scripts and importer tasks that are absent from the production artifact. |

The temp instance may share the same database as production. Destructive commands affect live data and require explicit user confirmation.

## Blue-Green Deployment Pattern

The Windows deployment workflow uses atomic symlink swapping for zero-downtime deployment:

1. Extract the release artifact into a timestamped staging directory under the configured production deployment root.
2. Link persistent storage into the staged release.
3. Rename the current production symlink to a temporary backup name.
4. Rename the new staging symlink to the production symlink.
5. On failure, roll back by restoring the backup symlink.
6. Clean up the temporary backup and prune old staging directories.

Never bypass this pattern with direct file replacement in the production directory.

## Deployment Flow

The Windows deployment workflow:

1. Downloads the release asset created by the build workflow.
2. Extracts it to the runner temp area and copies it to a staging directory.
3. Puts Laravel into maintenance mode.
4. Sets up shared storage symlinks.
5. Performs the atomic symlink swap.
6. Generates `.env` from template plus GitHub Environment variables and secrets.
7. Runs `php artisan migrate --force`.
8. Runs permission synchronization and Laravel cache warmup.
9. Brings Laravel out of maintenance mode.
10. Cleans up swap symlinks and old releases.

## GitHub Environment

Deployment configuration belongs in the configured GitHub Environment for the Windows target.

Use GitHub Environment variables for non-secret deployment settings such as PHP path, webserver symlink path, app name, app environment, app URL, database host, and service user.

Use GitHub Environment secrets for production secrets such as Laravel app key and database credentials.

Never commit production secrets to Git. Never expose internal server paths or credentials in logs; mask sensitive values.

## Deployment Module

The `scripts/InventoryApp.Deployment/` PowerShell module provides reusable deployment functions:

| Function | Purpose |
|----------|---------|
| `Test-SystemPrerequisites` | Verify PHP, paths, and permissions. |
| `Test-DeploymentPackage` | Validate extracted release. |
| `New-StagingDirectory` | Create timestamped staging directory. |
| `Remove-OldStagingDirectories` | Prune old releases. |
| `New-StorageSymlink` | Link shared storage into release. |
| `Swap-WebserverSymlink` | Atomic symlink swap. |
| `Remove-SwapBackup` | Cleanup backup symlink. |
| `New-EnvironmentFile` | Generate `.env` from template. |
| `Invoke-LaravelSetup` | Run migrations and cache commands. |
| `Invoke-LaravelDown` | Maintenance mode. |
| `Deploy-Application` | Full orchestration. |

## Apache Configuration

When inspecting or editing Apache paths, resolve concrete paths from `.copilot/local/server-windows.md` first. Do not hard-code a contributor's server root or private vhost paths in shared instructions.

## Forbidden

- Never modify Apache config, restart services, or install software in deploy scripts or workflows. Infrastructure changes belong in manual provisioning only.
- Never run destructive database commands (`migrate:fresh`, `migrate:refresh`, `db:wipe`) in production workflows. Use `migrate --force` only.
- Never commit production secrets to Git. Use GitHub Environment secrets and variables.
- Never bypass the blue-green symlink pattern.
- Never use the temp instance path in `deploy.yml`; it is for manual script execution only.
- Never expose internal server paths or credentials in logs.
