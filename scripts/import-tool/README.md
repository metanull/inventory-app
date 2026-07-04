# Import Tool

Dockerized legacy-data import pipeline against the OVH-hosted `inventory-app`.
Runs the [importer](../importer/README.md) (`import`, `image-sync`) over an SSH
tunnel and drives the remote `artisan` commands over blocking SSH calls, no 
manual `scp`, no dependency on `rsync` existing on the operator's machine.

## Run (TL;DR)

```powershell
$env:CONFIRM_WIPE='yes-really-wipe-production'
docker compose -f scripts/import-tool/docker-compose.yml run --build --rm clean
```

`--build` matters: `docker compose run` only builds the image if it
doesn't exist yet — it does **not** notice that `entrypoint.sh` or the
`importer` source changed since the last build, and will silently keep
running the old one. Always include `--build` after pulling changes to
this tool (or run `docker compose -f scripts/import-tool/docker-compose.yml
build` once beforehand) to be sure you're running current code.

## Modes

| Mode | What it does |
|---|---|
| `append` (default) | `import` → `image-sync` → `glossary:resync`, no wipe. Safe to run any time — the importer is idempotent, this only adds what's missing. |
| `backup-permissions` | Snapshots users (incl. MFA columns), role assignments, direct permission assignments, and API tokens to an encrypted JSON on the OVH host, plus a redundant timestamped copy pulled back locally. No import, no writes to application data. |
| `clean` | `db:wipe` → `migrate` → `db:seed` → `permission:sync` → restore users → the full `append` pipeline. **Destructive.** Requires `CONFIRM_WIPE=yes-really-wipe-production` or it refuses to run. Restores from a snapshot at `AUTH_SNAPSHOT_REMOTE` if one exists (run `backup-permissions` first to create it — `clean` does not take a fresh snapshot itself); if none exists **and** both `ADMIN_EMAIL`/`REGULAR_USER_EMAIL` are set, falls back to recreating just those two accounts instead. If neither applies, aborts before anything beyond the wipe itself is lost. |
| `stage` | `import` → `image-sync`, writing straight to a local, persistent MySQL (`local-mysql`) instead of through the OVH tunnel. **No SSH, no OVH contact at all.** Builds a fully-populated local copy of the legacy import — DB rows in `local-mysql`, image files in the `local-images-data` volume — at local-network speed. Safe to run at any time, including while a real `append`/`clean` run is in progress against OVH (they hit the same legacy source DB, which comfortably handles concurrent readers). See "Local staging" below. |

## Build

Build context must be the **repo root** (the Dockerfile copies
`scripts/importer`):

```bash
docker build -f scripts/import-tool/Dockerfile -t import-tool:latest .
```

## Configure

```bash
cp scripts/import-tool/.env.example scripts/import-tool/.env
# edit scripts/import-tool/.env with real credentials/paths
```

The file must be named exactly `.env` (not e.g. `.env.import-tool`) —
Docker Compose only auto-loads a file with that literal name to resolve
`${VAR}` placeholders while parsing `docker-compose.yml` itself (used
below for the `volumes:` host paths), which is a separate step from
`env_file:` injecting variables into the container. A differently-named
file works for the container's own environment but silently leaves the
volume paths empty, which Docker then rejects with something like
`invalid spec: :/run/secrets/deploy_key:ro: empty section between colons`.

`.env` is gitignored. See `.env.example` for every variable and what
it's for — the important ones:

- `OVH_SSH_KEY_HOST_PATH` — host path to the `deploy` SSH private key
- `LEGACY_IMAGES_HOST_PATH` — host path to the legacy images source
  (whatever `Z:\mwnf\images` resolves to on your machine)
- `DB_*` — target DB, reached through the tunnel this container opens
  (`DB_HOST=127.0.0.1`, `DB_PORT` matching `TUNNEL_LOCAL_PORT`, default `3307`)
- `LEGACY_DB_*` — legacy source DB
- `CONFIRM_WIPE` — leave commented out; only set for a real `clean` run

## Run
Via compose (recommended — reads `.env` for you). Include `--build` any
time `entrypoint.sh` or the importer source has changed since your last
build — `docker compose run` does not detect that on its own:

```bash
docker compose -f scripts/import-tool/docker-compose.yml run --build --rm append
docker compose -f scripts/import-tool/docker-compose.yml run --build --rm backup-permissions
docker compose -f scripts/import-tool/docker-compose.yml run --build --rm clean
```

Or raw `docker run` (Windows paths shown):

```bash
docker run --rm \
  -e OVH_HOST=51.75.246.163 -e OVH_USER=deploy \
  -e DB_HOST=127.0.0.1 -e DB_PORT=3307 -e DB_USERNAME=inventory -e DB_PASSWORD=*** -e DB_DATABASE=inventory \
  -e LEGACY_DB_HOST=192.168.255.157 -e LEGACY_DB_USER=havelpa -e LEGACY_DB_PASSWORD=*** -e LEGACY_DB_DATABASE=mwnf3 \
  -v C:\users\phave\.ssh\inventory_deploy:/run/secrets/deploy_key:ro \
  -v Z:\mwnf\images:/legacy-images:ro \
  -v E:\inventory\auth-backups:/backup \
  import-tool:latest append
```

## Before your first `clean`

Best case: run `backup-permissions` at least once before your first
`clean`, so there's a real snapshot to restore from — this preserves
*every* existing user, MFA setup, and role/permission assignment exactly
as they were.

If you skip that (or the snapshot is missing for any other reason),
`clean` checks whether `AUTH_SNAPSHOT_REMOTE` actually exists on the OVH
host *before* calling `auth:restore` — it does not just let the restore
fail and stop there. If nothing exists and both `ADMIN_EMAIL` and
`REGULAR_USER_EMAIL` are set (see `.env.example`), it recreates exactly
those two accounts instead — one with the `Manager of Users` role
(admin panel + user/role/settings management), one with `Regular User`
(data operations only). Neither account gets a plaintext password:
`user:create` emails a password-reset invitation to that address, so you
still need working outbound mail on OVH for this to be usable.

If neither a snapshot nor both fallback emails are available, `clean`
aborts right there — after the wipe, but before anything else happens —
with a clear message telling you what's missing. **Note this means a
`clean` run without a snapshot and without the fallback configured still
leaves the `users` table empty** (`db:seed`'s `UserSeeder` only creates
accounts when `APP_ENV=local`, i.e. never on production) — there is no
way to log into `/admin` again until you either supply a snapshot or set
the fallback emails and re-run.

## Dry runs

Set `DRY_RUN=1` to pass `--dry-run` through to the importer's `import`
and `image-sync` steps and skip the `glossary:resync` SSH call and the
rsync push entirely — nothing is written anywhere. Does not apply to
`clean`'s wipe/restore steps (there is no dry-run for those; test wipe
semantics against a disposable environment instead, see "Local testing"
below).

## Local testing (no OVH contact)

1. Build the image and confirm it succeeds (the riskiest step is
   `better-sqlite3`'s native build on Alpine's musl libc — if `npm ci`
   fails here, switch the base image to `node:22-slim` and
   `apt-get install -y openssh-client rsync` instead of `apk add`).
2. Set `IMPORT_TOOL_SKIP_TUNNEL=1` and point `DB_HOST`/`DB_PORT` directly
   at a disposable local MySQL container (see the `mysql` service in the
   repo root `docker-compose.yml` for the pattern already used elsewhere
   in this project) instead of opening a real SSH tunnel — exercises
   `run_importer_import`/`run_image_sync_and_push` without any SSH
   involved. `OVH_HOST` is still required to satisfy the entrypoint's
   validation even in this mode; any placeholder value works since it's
   never actually dialed.
3. To check the `--delete` gating and per-branch exit-code handling in
   `entrypoint.sh` without touching the importer at all, put stub
   `ssh`/`rsync` scripts earlier in `$PATH` that log their arguments (or
   exit non-zero to force a failure) and run the container for both
   `append` and `clean` — confirm `--delete` shows up only for `clean`,
   and that a forced failure in one parallel branch (image-sync/rsync vs
   glossary-resync) still lets the other complete and the container exits
   non-zero with a clear message either way.

## Local staging

`stage` exists because every operation `append`/`clean` perform against the
real target DB goes over an SSH tunnel to OVH — for a full import, that's
tens of thousands of small round-trips at WAN latency, which is why a real
`clean` run can take hours. `stage` writes to a local MySQL instead (`local-mysql`,
same `mysql:8.4` image the root dev `docker-compose.yml` uses), removing that
per-row network cost entirely. It's a way to build and inspect a fully-populated
copy of the import fast, and — later — a way to prepare data for a bulk
`mysqldump` + `rsync` handoff to OVH instead of thousands of live inserts
against production. **That handoff is a manual runbook, not automated
yet** — see "Shipping a staged build to a server" below; `stage` itself
only builds and holds the local copy.

```bash
docker compose -f scripts/import-tool/docker-compose.yml up -d local-mysql
docker compose -f scripts/import-tool/docker-compose.yml run --build --rm stage
```

`local-mysql` doesn't need to be started explicitly first — `run --rm stage`
brings up its dependencies (`local-mysql`, then the one-shot `local-migrate`)
automatically — but starting it yourself first lets you watch its healthcheck
separately and keeps it running independently of any one `stage` invocation.

Both the DB and the staged images persist in named Docker volumes
(`local-mysql-data`, `local-images-data`) — they survive `docker restart`,
and survive `docker compose down` (without `-v`) followed by `up`/`run` again.
Only `docker compose down -v` (or `docker volume rm`) destroys them.

`stage` intentionally differs from `append`/`clean` in two ways:

- **Migrations only, no seeders.** `local-migrate` runs `php artisan migrate --force`
  and nothing else — no roles, no permissions, no dev users. This container
  is never meant to be logged into; it exists purely to materialize a
  fully-populated dataset, so there's no reason to wire up auth for it.
- **Non-fatal on partial errors.** The importer's `import` and `image-sync`
  commands both exit non-zero whenever *any* row/file failed — which, for
  this legacy dataset, is always true (a handful of genuinely bad legacy rows,
  and some image paths referenced in the legacy DB that don't actually exist
  under `LEGACY_IMAGES_HOST_PATH`). `append`/`clean` still treat that as fatal
  (unchanged, on purpose — see `run_importer_import`/`run_image_sync_and_push`
  in `entrypoint.sh`). `stage` uses tolerant variants that log a note and
  continue instead, since the goal is "best complete copy possible," not
  "abort over a known, already-summarized handful of legacy gaps." Every
  skipped row/file is still listed in the run's own output and log file —
  nothing is silently swallowed, just not treated as pipeline-fatal.

Rerunning `stage` against an already-populated `local-mysql` is safe and
fast — `import` and `image-sync` are both idempotent, so a rerun only adds
what's missing (e.g. after new legacy data shows up) instead of redoing
everything.

## Shipping a staged build to a server (manual — not automated yet)

Once `stage` has produced a fully-populated `local-mysql` + `local-images-data`,
you can hand that off to a target server (OVH or otherwise) instead of
running `append`/`clean` against it directly over the tunnel. There is no
mode that does this for you yet — it's a manual runbook:

1. **Dump the local DB:**
   ```bash
   docker exec inventory-import-tool-local-mysql-1 \
     mysqldump -uinventory -psecret --single-transaction --routines inventory \
     > inventory-staged.sql
   ```
2. **Copy the dump and the staged images to the server.** The dump is a
   plain file, so `scp`/`rsync` it directly. The images live inside the
   `local-images-data` *volume*, not a host directory — on Docker Desktop
   (Windows/Mac) that volume is inside the Docker VM, so copy it out via a
   throwaway container first, then rsync the resulting host directory:
   ```bash
   docker run --rm -v inventory-import-tool_local-images-data:/data -v <host-dir>:/out alpine cp -a /data/. /out/
   rsync -az inventory-staged.sql deploy@<server>:/tmp/
   rsync -az --stats <host-dir>/ deploy@<server>:/opt/inventory/shared/storage/app/public/pictures/
   ```
3. **Load the dump into the target DB on the server**, replacing its
   contents — treat this exactly like `clean`'s wipe: it's a full
   replacement, so back up first and schedule a maintenance window
   (`php artisan down`) around it:
   ```bash
   ssh deploy@<server> "mysql -u<db_user> -p<db_pass> <db_name> < /tmp/inventory-staged.sql"
   ```
4. **Finish up on the server:** `php artisan storage:link` (if not already
   linked) and `php artisan optimize:clear`, then bring it back up
   (`php artisan up`).

## Testing against real OVH

Test in this order, each step safer than the next:

1. `backup-permissions` — read-only against the DB, non-destructive.
2. A tunnel-only smoke test with `DRY_RUN=1 append` — confirms the SSH
   tunnel opens and `mysql2` can actually reach the real OVH-side MySQL,
   without writing anything.
3. `append` for real — safe next step since `import` is idempotent.
4. `clean` — only after 1–3 are all clean, and only with a known-good
   snapshot already at `AUTH_SNAPSHOT_REMOTE`.
