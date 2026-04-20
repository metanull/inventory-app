---
description: "Use when: configuring the OVH VPS, writing or modifying OVH deployment scripts, editing deploy-ovh workflow, managing Nginx vhost for inventory, SSH access to the VPS, queue worker setup, or MySQL backup for the inventory-app on the shared OVH server."
applyTo: "scripts/deploy-ovh.sh,scripts/provision-inventory.sh,.github/workflows/deploy-ovh.yml"
---

# Server Setup — OVH VPS (Secondary Deployment)

The inventory-app is deployed to a shared OVH VPS as a **secondary** deployment target. The **primary** deployment is to the MWNF Windows server via the `deploy.yml` workflow and a self-hosted GitHub Actions runner.

## Topology

The OVH VPS is shared with the Motivya app (separate project, out of scope). Both apps run on the same server but are fully isolated at the application level.

```
┌──────────────────────────────────────────────────────────┐
│  OVH VPS Starter (France)                                │
│  1 vCPU · 2 GB RAM · 20 GB SSD                          │
│  Ubuntu 24.04 LTS                                        │
│                                                          │
│  PHP 8.4-FPM · Nginx · MySQL · Valkey                   │
│  (installed once by motivya's provision.sh)              │
│                                                          │
│  ┌────────────────────┐  ┌─────────────────────────────┐ │
│  │ Motivya            │  │ Inventory App               │ │
│  │ motivya.metanull.eu│  │ inventory.metanull.eu       │ │
│  │ /opt/motivya/      │  │ /opt/inventory/             │ │
│  │ Docker Compose     │  │ Native PHP-FPM + Nginx      │ │
│  │ Redis DB 0/1       │  │ Valkey DB 2/3               │ │
│  │ MySQL: motivya     │  │ MySQL: inventory            │ │
│  └────────────────────┘  └─────────────────────────────┘ │
│                                                          │
│  Certbot (Let's Encrypt) — auto-renewing                 │
│  UFW firewall: 22, 80, 443 only                         │
└──────────────────────────────────────────────────────────┘
```

**Key difference from Motivya:** Inventory-app runs natively on PHP-FPM + Nginx — there is NO Docker involved. Never suggest Docker Compose, Dockerfiles, or container-based deployment for the inventory-app.

## Domain & DNS

- **Domain**: `inventory.metanull.eu` (CNAME → `metanull.eu`)
- **SSL**: Let's Encrypt via Certbot, auto-renewing

## Two-Account Model

The VPS has exactly two user accounts:

| Account  | Purpose                  | SSH key                  | sudo   | Used by              |
|----------|--------------------------|--------------------------|--------|----------------------|
| `ubuntu` | System administration    | `~/.ssh/ubuntu`          | Yes    | Human operator only  |
| `deploy` | Application deployment   | `~/.ssh/inventory_deploy`| **No** | CD pipeline + human  |

**Rules:**
- `ubuntu` is NEVER used in GitHub Actions or deploy scripts — manual admin only.
- `deploy` has NO sudo — owns `/opt/inventory/` and deploys without elevation.
- The inventory-app uses its own SSH key (`~/.ssh/inventory_deploy`), distinct from Motivya's `~/.ssh/motivya_deploy`, to avoid mixing concerns.

## Application Directory

Owned by `deploy:www-data`. No root/sudo operations during deployment.

```
/opt/inventory/
├── current -> releases/<timestamp>/   # Symlink to active release (atomic swap)
├── releases/                          # Timestamped release directories (keep last 5)
├── shared/
│   ├── .env                           # Production .env (not in Git)
│   └── storage/                       # Laravel storage (symlinked into releases)
│       ├── app/public/
│       ├── framework/cache/data/
│       ├── framework/sessions/
│       ├── framework/views/
│       └── logs/
├── backups/                           # Daily MySQL dumps (14-day retention)
└── backup-db.sh                       # Backup script (cron at 3:30 AM)
```

## Redis/Valkey Isolation

Inventory-app uses Valkey DB 2 and 3 to avoid collision with Motivya (DB 0 and 1):
- `REDIS_DB=2` — sessions, queues
- `REDIS_CACHE_DB=3` — cache

## Scripts

| Script | Run as | Purpose |
|--------|--------|---------|
| `scripts/provision-inventory.sh` | root (once) | MySQL DB/user, `/opt/inventory/` structure, Nginx vhost, SSL, queue worker systemd unit, daily backup cron |
| `scripts/deploy-ovh.sh` | deploy (each deploy) | Extract release, symlink storage, configure `.env`, migrate, warm caches, restart queue, prune old releases, health check |

Both scripts are idempotent.

## Deployment Flow

Deploy is **artifact-based** — no git, composer, or npm on the VPS.

```
Push to main
    │
    ▼
Build workflow (build.yml) — runs on ubuntu-latest
  → produces release.tar.gz artifact
    │ (success)
    ▼
Deploy to OVH workflow (deploy-ovh.yml) — triggered by workflow_run
  1. Download release artifact from Build run
  2. SSH pre-checks (netcat + whoami) — fail fast
  3. SCP release.tar.gz + deploy-ovh.sh to VPS /tmp/
  4. SSH: bash deploy-ovh.sh /tmp/inventory-release.tar.gz
  5. Cleanup /tmp/ files
    │
    ▼
VPS: deploy-ovh.sh (runs as deploy, NO sudo)
  1. Extract to /opt/inventory/releases/<timestamp>/
  2. Symlink shared/storage into release
  3. Create/symlink .env (first deploy only)
  4. php artisan migrate --force
  5. config:cache, route:cache, view:cache
  6. php artisan queue:restart
  7. Swap /opt/inventory/current symlink (atomic)
  8. Prune old releases (keep last 5)
  9. Health check (curl localhost with Host header)
```

## GitHub Environment Secrets

Stored in the **`inventory.metanull.eu`** GitHub Environment (Settings → Environments):

| Secret         | Value                                      | Used by           |
|----------------|--------------------------------------------|--------------------|
| `VPS_HOST`     | VPS IP address                             | `deploy-ovh.yml`   |
| `VPS_SSH_KEY`  | Private SSH key for `deploy` (inventory_deploy) | `deploy-ovh.yml` |
| `VPS_SSH_USER` | `deploy`                                   | `deploy-ovh.yml`   |

Application secrets (DB password, etc.) live in `/opt/inventory/shared/.env` on the VPS — never in GitHub.

## Queue Worker

Systemd service `inventory-queue.service` runs as `www-data`:
- `php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --memory=128`
- Logs to `/opt/inventory/shared/storage/logs/queue-worker.log`
- Restarted gracefully via `php artisan queue:restart` after each deploy

## Backup Strategy

- **Database**: Daily mysqldump cron at 3:30 AM → `/opt/inventory/backups/inventory-YYYY-MM-DD.sql.gz`
- **Retention**: 14 days
- **Code**: Git is the source of truth — no code backup needed

## Forbidden

- **NEVER use `sudo` in deploy scripts or the deploy-ovh workflow.** `deploy` owns `/opt/inventory/` — no elevation needed.
- **NEVER use the `ubuntu` account in GitHub secrets, workflows, or automated scripts.**
- **NEVER install packages (`apt-get`) in deploy scripts.** Dependencies are installed once via `provision-inventory.sh`.
- **NEVER use Docker** for the inventory-app — it runs natively on PHP-FPM + Nginx.
- **NEVER run `migrate:fresh` or `migrate:refresh` in production** — only `migrate --force`.
- Do NOT expose MySQL or Valkey ports to the internet — localhost only.
- Do NOT store production secrets in Git or GitHub Secrets (except the SSH key for deploy).
