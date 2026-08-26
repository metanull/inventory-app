# Import Tool

Dockerized pipeline that imports legacy museum data into `inventory-app`, in
two phases that never overlap:

1. **`stage`** reads the legacy database and image tree and builds a complete,
   fully-populated copy locally — `staging-mysql` plus the staging images
   volume. It has no SSH key and cannot reach the deployed server.
2. **`ship`** takes that local copy and sends it to the deployed server as one
   bulk handoff. It has no legacy database credentials and no legacy image
   mount, and cannot read the legacy source.

Nothing goes from legacy straight to production. The separation is structural,
not conventional: it is enforced by what each service is given in `compose.yml`,
so neither phase *can* do the other's job.

A useful side effect is that the staged copy is a complete offline clone you
can browse, export from, and rework without a VPN — the `staging` profile
serves it at <http://localhost:8020> and the exporters run against it (see the
root [README](../../README.md)).

All commands below are run with Docker Compose from the repo root.

## Prerequisites

- **VPN to the legacy network**, connected and left up for the whole run —
  needed by `stage`, which reads the legacy source database (`LEGACY_DB_HOST`).
  This tool never manages the VPN; connect it yourself first.
- **SSH key** for the remote deploy host, and the host reachable on port 22 —
  needed by `ship` and `backup-permissions`. Not given to `stage`.
- `.env` file:
  ```bash
  cp scripts/import-tool/.env.example scripts/import-tool/.env
  # edit scripts/import-tool/.env with real credentials/paths
  ```

### The `--env-file` flag is not optional

These services live in the root `compose.yml` under the `import` profile, but
their credentials do **not**. Every command below therefore passes
`--env-file scripts/import-tool/.env` explicitly:

```bash
docker compose --env-file scripts/import-tool/.env --profile import run --rm <service>
```

Compose auto-loads only the *project root's* `.env`, which here is the Laravel
application's own environment file — nothing to do with the importer. Without
the flag, `${OVH_SSH_KEY_HOST_PATH}` and friends fall back to their harmless
placeholder defaults and the run fails with a validation error from
`entrypoint.sh` rather than doing anything dangerous.

Keeping the file out of the project root is deliberate: it holds production
credentials and a path to the legacy image tree, and a bare `docker compose up`
must never be able to pick them up.

Variables (see `.env.example` for the full list with inline comments):

| Variable | Purpose |
|---|---|
| `OVH_HOST` / `OVH_USER` | Remote deploy host and SSH user. Named `OVH_*` for historical reasons — works with any Linux host you configure here. |
| `OVH_SSH_KEY_HOST_PATH` | Host path to the deploy SSH private key. |
| `OVH_APP_DIR` / `OVH_SHARED_DIR` | Paths on the remote host (app directory, shared/persistent storage). |
| `LEGACY_DB_*` | The legacy source database, reached over your VPN. Read by `stage` only. |
| `LEGACY_IMAGES_HOST_PATH` | Host path to the legacy images source, mounted read-only into `stage`. |
| `CONFIRM_WIPE` | Required verbatim (`yes-really-wipe-production`) for `ship` to proceed. Leave unset in `.env`; pass it inline on the command line only when you mean it. |
| `ADMIN_EMAIL` / `REGULAR_USER_EMAIL` | Fallback accounts `ship` creates if no auth snapshot exists — see "Auth restore" below. |

The staged database is configured in `compose.yml`, not here: `stage` writes to
the `staging-mysql` service and `ship` dumps from it. The importer's own
configuration has no route to a production database at all.

## The import, end to end

```bash
# 1. Build the local copy from legacy (VPN up; no contact with the server).
docker compose --env-file scripts/import-tool/.env --profile import run --build --rm stage

# 2. Compute glossary links against that local copy.
docker compose --env-file scripts/import-tool/.env --profile import run --build --rm staging-glossary-sync

# 3. Optional but recommended: review it locally before shipping.
docker compose --profile staging up -d          # http://localhost:8020

# 4. Send it to the server. DESTRUCTIVE — wipes and rebuilds the deployed app.
$env:CONFIRM_WIPE = 'yes-really-wipe-production'
docker compose --env-file scripts/import-tool/.env --profile import run --build --rm ship
```

Run `stage` and `staging-glossary-sync` again any time to pick up new/changed
legacy data — both are safe to rerun against an already-populated
`staging-mysql` (idempotent import, and `staging-glossary-sync` always
recomputes glossary links from scratch). Only `ship` touches the remote
server, and only `ship` needs `CONFIRM_WIPE`.

Incremental updates are the same two steps: re-`stage`, then re-`ship`. There
is no partial-push mode, on purpose — a `ship` always rebuilds the deployed
dataset from the staged copy, so what is deployed is exactly what you reviewed
locally.

To start completely over locally, destroy the two staging volumes by hand:

```bash
docker volume rm inventory-import-tool_local-mysql-data inventory-import-tool_local-images-data
docker volume create inventory-import-tool_local-mysql-data
docker volume create inventory-import-tool_local-images-data
```

They are declared `external:` in `compose.yml` exactly so that a stray
`docker compose down -v` cannot do this for you: a full stage is hours of
importing and several gigabytes of images. The names are the ones the old
`scripts/import-tool` stack used, kept verbatim so an already-populated staging
clone survived the move into the root compose file.

Before a destructive re-stage, the staged copy is worth backing up — it is
cheaper to restore than to re-import:

```bash
docker compose exec staging-mysql mysqldump -u inventory -psecret inventory > staging-backup.sql
docker run --rm -v inventory-import-tool_local-images-data:/data -v ${PWD}:/out alpine \
  tar czf /out/staging-images.tar.gz -C /data .
```

## Commands reference

| Command | Touches remote server? | What it does |
|---|---|---|
| `stage` | No — has no SSH key | Imports legacy data + syncs images into `staging-mysql` and the staging images volume. No auth/seeders: the staged database has no users (add one locally with the `staging-seed-auth` service if you want to browse it). |
| `staging-glossary-sync` | No | Computes glossary term ↔ item/collection/timeline-event links against `staging-mysql`, in-container, via `glossary:bulk-resync` (one combined pattern per language, one pass per translation — a couple of minutes, vs. over a day for `glossary:resync`'s one-job-per-spelling approach). Run after `stage`, before `ship`. |
| `ship` | Yes, **destructive** | Rebuilds the remote app layer (`db:wipe` → `migrate` → `db:seed` → `permission:sync` → restore/recreate users), then loads `staging-mysql`'s content tables (dumped and restored directly on the remote host) and pushes the already-staged images. Requires `stage` (+ `staging-glossary-sync`) to have populated the staging volumes. Requires `CONFIRM_WIPE`. |
| `backup-permissions` | Yes (read + write snapshot) | Snapshots users (incl. MFA), roles, permissions, and API tokens to an encrypted file on the remote host, plus a timestamped local copy. Read-only against application data. Run it before your first `ship`. |

### Dry runs

Set `DRY_RUN=1` to pass `--dry-run` through to `stage`'s `import` and
`image-sync` steps — nothing is written anywhere. Not available for `ship`'s
wipe/restore steps.

## Auth restore (`ship`)

`ship` rebuilds the remote app's users/roles/permissions from scratch, then
restores them:

1. If a snapshot exists at `AUTH_SNAPSHOT_REMOTE` (created by
   `backup-permissions`), it's restored exactly — every user, MFA setup, and
   role/permission assignment as they were.
2. Otherwise, if both `ADMIN_EMAIL` and `REGULAR_USER_EMAIL` are set,
   exactly those two accounts are recreated (`Manager of Users` and
   `Regular User` roles respectively). Neither gets a plaintext password —
   `user:create` emails a password-reset invitation, so outbound mail must
   work on the remote host.
3. If neither applies, the run aborts right after the wipe, with a clear
   message — the `users` table is left empty and nothing else runs. Run
   `backup-permissions` at least once beforehand to avoid this.

Nothing from the staged database is involved: `ship`'s dump excludes `users`,
`roles`, `permissions`, tokens and sessions outright (`SHIP_EXCLUDED_TABLES`
in `entrypoint.sh`), so accounts created locally — by `staging-seed-auth`, say
— cannot reach the server.

## Testing against the real remote server

In order of increasing risk:

1. `backup-permissions` — read-only against application data.
2. `DRY_RUN=1 stage` — confirms the legacy source is reachable and the
   importer runs, without writing anything.
3. `stage` for real — local only, nothing remote can be affected.
4. Review the result at <http://localhost:8020>.
5. `ship` — only after 1–4 are clean, and only with a known-good snapshot
   already at `AUTH_SNAPSHOT_REMOTE`.
