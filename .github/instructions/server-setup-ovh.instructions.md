---
description: "Use when: configuring the secondary VPS deployment, writing or modifying OVH deployment scripts, editing deploy-ovh workflow, managing Nginx vhost, SSH access, queue worker setup, or MySQL backup for the inventory-app on the shared VPS server."
applyTo: "scripts/deploy.sh,scripts/provision.sh,.github/workflows/deploy-ovh.yml"
---

# Server Setup: Secondary VPS Deployment

The inventory-app has a secondary VPS deployment target. Shared instructions describe the deployment model and safety rules. Contributor-specific host aliases, SSH key paths, account names, concrete server paths, and private access details live in `.copilot/local/server-ovh.md`.

Before using a host, SSH account, key path, app root, log path, or Nginx path, read `.copilot/local/server-ovh.md` first. If the file is missing or incomplete, ask the user for the missing value before executing commands or writing examples. Do not guess hostnames, key paths, account names, app roots, or service names.

Use `.copilot/local/server-ovh.template.md` as the collaborator-facing schema. Do not store passwords, private key contents, tokens, or production `.env` values in local config.

## Required Local Config Keys

- `host`
- `deploy_user`
- `deploy_ssh_key`
- `admin_user`
- `admin_ssh_key`
- `app_root`
- `current_symlink`
- `releases_root`
- `shared_root`
- `shared_env`
- `shared_storage`
- `shared_pictures_dir`
- `laravel_log`
- `queue_worker_log`
- `backups_root`
- `domain`
- `nginx_vhost`
- `queue_service`
- `redis_db`
- `redis_cache_db`
- `db_credentials_path`

## Topology Model

The VPS target is a secondary deployment. It is artifact-based and should not require git, composer, or npm on the VPS during deployment.

The inventory app runs natively on PHP-FPM and Nginx. Never suggest Docker Compose, Dockerfiles, or container-based deployment for this target unless the project runbook changes.

Use local config keys rather than hard-coded paths:

| Area | Local config key |
|------|------------------|
| App root | `app_root` |
| Active release symlink | `current_symlink` |
| Releases root | `releases_root` |
| Shared root | `shared_root` |
| Shared `.env` | `shared_env` |
| Shared storage | `shared_storage` |
| Shared pictures | `shared_pictures_dir` |
| Laravel log | `laravel_log` |
| Queue worker log | `queue_worker_log` |
| Backups root | `backups_root` |
| Nginx vhost | `nginx_vhost` |
| Queue service | `queue_service` |

## Account Model

The VPS uses two account roles:

| Account role | Purpose |
|--------------|---------|
| Deploy account | Application deployment and day-to-day app inspection. No sudo. Owns the configured app tree. |
| Admin account | Human system administration only. Use only when sudo or system paths are required. |

Rules:

- Never use the admin account in GitHub Actions or automated deployment scripts.
- Never use sudo in deploy scripts or the deploy workflow.
- Use the deploy account for all app-level operations under the configured app root.
- Keep SSH private keys in local files or GitHub secrets, never in Git.

## Application Directory Model

The app tree contains:

- `current`: symlink to the active release.
- `releases`: timestamped release directories.
- `shared`: persistent `.env` and Laravel storage.
- `backups`: database backup artifacts.
- backup script and scheduled backup configuration.

Use configured paths from `.copilot/local/server-ovh.md` when constructing commands.

## Valkey Isolation

Inventory-app uses configured Valkey DB indexes to avoid collision with other apps on the same VPS. Read `redis_db` and `redis_cache_db` from local config before inspecting or flushing anything.

## Scripts

| Script | Run as | Purpose |
|--------|--------|---------|
| `scripts/provision.sh` | root/admin during one-time provisioning | Create DB/user, app structure, Nginx vhost, TLS, queue worker, and backups. |
| `scripts/deploy.sh` | deploy account for each deploy | Extract release, link storage, configure `.env`, migrate, warm caches, restart queue, prune old releases, and health check. |

Both scripts should remain idempotent.

## Deployment Flow

The VPS deployment flow:

1. Build workflow produces a release tarball artifact.
2. Deploy workflow downloads the release artifact.
3. Deploy workflow performs SSH pre-checks.
4. Deploy workflow copies release artifact and deploy script to the VPS temp area.
5. Deploy workflow runs the deploy script as the deploy account.
6. Deploy script extracts to a timestamped release directory.
7. Deploy script symlinks shared storage and `.env`.
8. Deploy script runs `php artisan migrate --force` and cache warmup.
9. Deploy script restarts the queue gracefully.
10. Deploy script swaps the current symlink atomically.
11. Deploy script prunes old releases and performs a health check.

## GitHub Environment Secrets

Deployment configuration belongs in the configured GitHub Environment for the VPS target.

Use GitHub Environment secrets for the VPS host, SSH key, SSH user, and production secrets required by the workflow. Application secrets such as DB password belong in the server-side shared `.env`, not in Git.

## Queue Worker

The queue worker is managed by systemd under the configured `queue_service` name. Logs live at the configured `queue_worker_log` path. Restart it gracefully with Laravel queue restart commands during deployment.

## Backup Strategy

Database backup configuration belongs to provisioning. Keep backup scripts idempotent and write backup artifacts under the configured backup root. Do not hard-code a contributor's concrete backup path in shared instructions.

## Forbidden

- Never use sudo in deploy scripts or the deploy workflow.
- Never use the admin account in GitHub secrets, workflows, or automated scripts.
- Never install packages in deploy scripts; dependencies are installed during provisioning.
- Never use Docker for this inventory-app deployment unless the runbook changes.
- Never run `migrate:fresh` or `migrate:refresh` in production; use `migrate --force` only.
- Never expose MySQL or Valkey ports to the internet.
- Never store production secrets in Git.
