# Import Tool

Dockerized pipeline that imports legacy museum data into `inventory-app`'s
database and pushes the associated images and glossary links to a deployed
instance on a remote Linux server. All commands below are run with Docker
Compose from the repo root.

## Prerequisites

- **VPN to the legacy network**, connected and left up for the whole run —
  every mode reads from the legacy source database (`LEGACY_DB_HOST`), which
  is only reachable that way. This tool never manages the VPN; connect it
  yourself before running anything.
- **SSH key** for the remote deploy host, and the host itself reachable on
  port 22 — required by every mode except `stage`/`local-glossary-sync`
  (see "Two ways to import" below).
- `.env` file:
  ```bash
  cp scripts/import-tool/.env.example scripts/import-tool/.env
  # edit scripts/import-tool/.env with real credentials/paths
  ```
  Must be named exactly `.env` — Compose only auto-loads that literal
  filename to resolve `${VAR}` placeholders in `docker-compose.yml`'s
  `volumes:` section. A differently-named file still reaches the
  container's own environment but leaves those paths empty, which Docker
  rejects with `invalid spec: :/run/secrets/deploy_key:ro: empty section
  between colons`.

Variables (see `.env.example` for the full list with inline comments):

| Variable | Purpose |
|---|---|
| `OVH_HOST` / `OVH_USER` | Remote deploy host and SSH user. Named `OVH_*` for historical reasons — works with any Linux host you configure here. |
| `OVH_SSH_KEY_HOST_PATH` | Host path to the deploy SSH private key. |
| `OVH_APP_DIR` / `OVH_SHARED_DIR` | Paths on the remote host (app directory, shared/persistent storage). |
| `DB_*` | The remote app's database — reached through the SSH tunnel `append`/`clean` open (`DB_HOST=127.0.0.1`, `DB_PORT` matching `TUNNEL_LOCAL_PORT`, default `3307`). Not used by `stage`/`local-glossary-sync` (they always target `local-mysql` instead — see below). |
| `LEGACY_DB_*` | The legacy source database, reached over your VPN. |
| `LEGACY_IMAGES_HOST_PATH` | Host path to the legacy images source, mounted read-only. |
| `CONFIRM_WIPE` | Required verbatim (`yes-really-wipe-production`) for `clean`/`ship` to proceed. Leave unset in `.env`; pass it inline on the command line only when you mean it. |
| `ADMIN_EMAIL` / `REGULAR_USER_EMAIL` | Fallback accounts `clean`/`ship` create if no auth snapshot exists — see "Auth restore" below. |

## Two ways to import

### Scenario 1 — Direct: legacy → remote server

`append` and `clean` read the legacy database over your VPN and write
straight into the remote server's database, live, over an SSH tunnel this
container opens for the duration of the run. Every row is a WAN round trip,
so a full import takes hours; safe to run any time since it's idempotent
(rows already imported are skipped).

```bash
docker compose -f scripts/import-tool/docker-compose.yml run --build --rm append
docker compose -f scripts/import-tool/docker-compose.yml run --build --rm clean
```

### Scenario 2 — Staged: legacy → local container → remote server

`stage` builds a complete, fully-populated copy of the import locally
(database rows in a local `local-mysql` container, images in a local
volume) — no contact with the remote server at all. `local-glossary-sync`
then computes glossary term links against that local copy. `ship` sends the
finished result to the remote server as one bulk handoff: rebuild the app
layer fresh, load the local database's content tables in one dump/restore,
push the staged images, done. Much faster than Scenario 1 for a full or
from-scratch import, since none of the row-by-row work crosses the network.

```bash
docker compose -f scripts/import-tool/docker-compose.yml run --build --rm stage
docker compose -f scripts/import-tool/docker-compose.yml run --build --rm local-glossary-sync
$env:CONFIRM_WIPE = 'yes-really-wipe-production'
docker compose -f scripts/import-tool/docker-compose.yml run --build --rm ship
```

Run `stage` and `local-glossary-sync` again any time to pick up new/changed
legacy data — both are safe to rerun against an already-populated
`local-mysql` (idempotent import, and `local-glossary-sync` always
recomputes glossary links from scratch). Only `ship` touches the remote
server, and only `ship` needs `CONFIRM_WIPE`.

To start completely over locally: `docker compose -f scripts/import-tool/docker-compose.yml down -v`
destroys `local-mysql-data`/`local-images-data`/`local-app-vendor`.

## Commands reference

| Command | Touches remote server? | What it does |
|---|---|---|
| `backup-permissions` | Yes (read + write snapshot) | Snapshots users (incl. MFA), roles, permissions, and API tokens to an encrypted file on the remote host, plus a timestamped local copy. Read-only against application data. |
| `append` | Yes | Imports legacy data directly into the remote database (over the SSH tunnel), pushes new images via rsync, and runs `glossary:bulk-resync` on the remote host (synchronous, no queue). No wipe. |
| `clean` | Yes, **destructive** | `db:wipe` → `migrate` → `db:seed` → `permission:sync` → restore/recreate users → the same pipeline as `append`. Requires `CONFIRM_WIPE=yes-really-wipe-production`. See "Auth restore" below for the users/roles step. |
| `stage` | No | Imports legacy data + syncs images into local `local-mysql` / `local-images-data`. No auth/seeders — this container is never meant to be logged into. |
| `local-glossary-sync` | No | Computes glossary term ↔ item/collection/timeline-event links against `local-mysql`, in-container, via `glossary:bulk-resync` (one combined pattern per language, one pass per translation — a couple of minutes, vs. over a day for `glossary:resync`'s one-job-per-spelling approach). Run after `stage`, before `ship`. |
| `ship` | Yes, **destructive** | Rebuilds the remote app layer exactly like `clean`, then loads `local-mysql`'s content tables (dumped and restored directly on the remote host, not through the tunnel) and pushes the already-staged images. Requires `stage` (+ `local-glossary-sync`) to have already populated `local-mysql`/`local-images-data`. Requires `CONFIRM_WIPE`. |

### Dry runs

Set `DRY_RUN=1` to pass `--dry-run` through to the importer's `import` and
`image-sync` steps and skip the glossary resync and rsync push entirely —
nothing is written anywhere. Not available for `clean`/`ship`'s wipe/restore
steps.

## Auth restore (`clean` and `ship`)

Both destructive modes rebuild the remote app's users/roles/permissions from
scratch, then restore them:

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

## Local dev testing

Exercise `entrypoint.sh` without touching the legacy VPN or the remote
server:

- Set `IMPORT_TOOL_SKIP_TUNNEL=1` and point `DB_HOST`/`DB_PORT` at a
  disposable local MySQL container instead of opening a real SSH tunnel —
  exercises `append`'s import/image-sync path without any SSH involved.
  `OVH_HOST` still needs to be set to satisfy validation, but is never
  dialed.
- To check `--delete` gating and per-branch exit-code handling without
  running the importer at all, put stub `ssh`/`rsync` scripts earlier in
  `$PATH` that just log their arguments (or exit non-zero to force a
  failure), then run `append` and `clean` and confirm: `--delete` only
  appears for `clean`, and a forced failure in one parallel branch
  (image-sync/rsync vs. glossary-resync) still lets the other complete.

## Testing against the real remote server

In order of increasing risk:

1. `backup-permissions` — read-only against application data.
2. `DRY_RUN=1 append` — confirms the SSH tunnel opens and the remote
   database is reachable, without writing anything.
3. `append` for real — safe, since `import` is idempotent.
4. `clean` — only after 1–3 are clean, and only with a known-good snapshot
   already at `AUTH_SNAPSHOT_REMOTE`.
