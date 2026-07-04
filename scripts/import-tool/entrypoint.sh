#!/usr/bin/env bash
set -euo pipefail

# ==============================================================================
# Entrypoint for the inventory-app legacy import tool container.
#
# Modes (first arg or $IMPORT_MODE, default "append"):
#   append              import -> image-sync -> glossary-resync (idempotent,
#                       safe to re-run any time, adds only what's missing)
#   backup-permissions  snapshot users/MFA/roles/permissions to a fixed OVH
#                       path + a redundant local copy. No import, no writes
#                       to application data.
#   clean               db:wipe -> migrate -> seed -> permission:sync ->
#                       auth:restore -> [append pipeline]. DESTRUCTIVE.
#                       Requires CONFIRM_WIPE=yes-really-wipe-production.
#                       Restores from a snapshot at $AUTH_SNAPSHOT_REMOTE if
#                       one exists (run backup-permissions first to create
#                       it); if none exists AND both ADMIN_EMAIL and
#                       REGULAR_USER_EMAIL are set, falls back to recreating
#                       just those two accounts instead. If neither applies,
#                       aborts before anything is lost beyond the wipe
#                       itself.
#   stage               import -> image-sync, writing straight to DB_HOST
#                       (expected to be a local MySQL, e.g. the compose
#                       "local-mysql" service) instead of through the OVH
#                       tunnel. No SSH, no OVH contact at all — builds a
#                       fully-populated local copy (DB + staged images) at
#                       local-network speed.
#   ship                Ships an already-built `stage` copy to OVH: the same
#                       db:wipe -> migrate -> seed -> permission:sync ->
#                       auth:restore/fallback sequence as `clean` (so the app
#                       layer — users, roles, permissions, tokens — is always
#                       rebuilt fresh or restored from a real snapshot on OVH
#                       itself, NEVER read from local-mysql), followed by
#                       loading local-mysql's CONTENT tables only (legacy
#                       import data — explicitly excluding
#                       users/roles/permissions/tokens/sessions/etc., see
#                       SHIP_EXCLUDED_TABLES below) through the tunnel, then
#                       pushing the already-staged images and resyncing the
#                       glossary. DESTRUCTIVE — same CONFIRM_WIPE requirement
#                       as `clean`. Requires a `stage` run to have already
#                       populated local-mysql/local-images-data.
#
# See README.md for the full list of required env vars and mounts.
# ==============================================================================

IMPORTER_DIR=/opt/import-tool/importer

MODE="${1:-${IMPORT_MODE:-append}}"

# Only append/backup-permissions/clean/ship touch OVH — stage writes straight
# to a local DB_HOST and never dials out, so OVH_HOST has no reason to be
# required for it. Checked explicitly per-mode in the dispatch at the bottom
# instead of unconditionally here.
OVH_HOST="${OVH_HOST:-}"
require_ovh_host() { [ -n "$OVH_HOST" ] || die "OVH_HOST is required for mode '$MODE'"; }
OVH_USER="${OVH_USER:-deploy}"
OVH_APP_DIR="${OVH_APP_DIR:-/opt/inventory/current}"
OVH_SHARED_DIR="${OVH_SHARED_DIR:-/opt/inventory/shared}"
OVH_SSH_KEY_PATH="${OVH_SSH_KEY_PATH:-/run/secrets/deploy_key}"

TUNNEL_LOCAL_PORT="${TUNNEL_LOCAL_PORT:-3307}"
STAGING_DIR="${IMAGE_STAGING_DIR:-/staging/images}"

# `ship`-only: the LOCAL source DB built by a prior `stage` run. Distinct from
# DB_HOST/DB_PORT/DB_USERNAME/DB_PASSWORD/DB_DATABASE, which for `ship` (unlike
# `stage`) keep their normal append/clean meaning — the OVH target, reached
# through the tunnel this container opens.
LOCAL_DB_HOST="${LOCAL_DB_HOST:-local-mysql}"
LOCAL_DB_PORT="${LOCAL_DB_PORT:-3306}"
LOCAL_DB_USERNAME="${LOCAL_DB_USERNAME:-inventory}"
LOCAL_DB_PASSWORD="${LOCAL_DB_PASSWORD:-secret}"
LOCAL_DB_DATABASE="${LOCAL_DB_DATABASE:-inventory}"

# Tables NEVER included in a `ship` data load — every table that stores app
# identity/auth/session/queue/config state rather than imported legacy
# content. `stage`'s local-mysql never has real data in any of these (no
# seeders run there — see do_stage), so loading them wholesale onto OVH would
# blank out its real users, roles, permission assignments, API tokens, and
# settings. The real app-layer state is instead always (re)built directly on
# OVH by do_wipe_and_restore, using OVH's own APP_KEY, exactly like `clean`
# already does — never read from the local build. Reviewed against every
# migration that creates a users/auth/session/queue/settings table as of this
# writing; mysqldump --ignore-table is a no-op (not an error) for any of
# these that don't exist in a given schema version, so this list is safe to
# keep ahead of what's actually deployed.
SHIP_EXCLUDED_TABLES=(
  users password_reset_tokens sessions
  cache cache_locks
  jobs job_batches failed_jobs
  personal_access_tokens email_two_factor_codes
  permissions roles model_has_permissions model_has_roles role_has_permissions
  migrations settings
)

AUTH_SNAPSHOT_REMOTE="${AUTH_SNAPSHOT_REMOTE:-${OVH_SHARED_DIR}/auth-snapshots/current.json.enc}"
AUTH_SNAPSHOT_LOCAL_BACKUP_DIR="${AUTH_SNAPSHOT_LOCAL_BACKUP_DIR:-/backup}"

# clean-mode fallback, only used when no snapshot exists at AUTH_SNAPSHOT_REMOTE
# (see do_wipe_and_restore). Both must be set for the fallback to engage — if
# only one is set, or neither, clean fails with no snapshot the same way it
# always has, rather than partially creating accounts.
ADMIN_EMAIL="${ADMIN_EMAIL:-}"
REGULAR_USER_EMAIL="${REGULAR_USER_EMAIL:-}"

DRY_RUN="${DRY_RUN:-0}"

# Test-only escape hatch (see README.md "Local testing"): skips SSH entirely
# and assumes DB_HOST/DB_PORT already point at a reachable target DB.
# Never set this against a real OVH run.
SKIP_TUNNEL="${IMPORT_TOOL_SKIP_TUNNEL:-0}"

TUNNEL_PID=""
SSH_KEY=""

log() { printf '[import-tool] %s %s\n' "$(date -u +%FT%TZ)" "$*" >&2; }
die() {
  log "FATAL: $*"
  exit 1
}

# ------------------------------------------------------------------------------
# SSH key handling — bind-mounted files from a Windows host often arrive with
# permissive perms that OpenSSH refuses to use. Copy to a private, container-
# local path and lock it down before first use.
# ------------------------------------------------------------------------------
prepare_ssh_key() {
  [ "$SKIP_TUNNEL" = "1" ] && return 0
  [ -f "$OVH_SSH_KEY_PATH" ] \
    || die "SSH key not found at $OVH_SSH_KEY_PATH (mount it with -v <host-key>:$OVH_SSH_KEY_PATH:ro)"
  SSH_KEY=/tmp/deploy_key
  cp "$OVH_SSH_KEY_PATH" "$SSH_KEY"
  chmod 600 "$SSH_KEY"
}

SSH_OPTS_BASE=(-o StrictHostKeyChecking=accept-new -o BatchMode=yes -o ConnectTimeout=10)

# Blocking remote command. Fails the whole pipeline immediately on a non-zero
# exit — replaces the old script's fire-and-forget Start-Process calls, which
# had no ordering or completion guarantee at all.
ssh_run() {
  local cmd="$1"
  log "SSH: $cmd"
  ssh "${SSH_OPTS_BASE[@]}" -i "$SSH_KEY" "${OVH_USER}@${OVH_HOST}" \
    "cd '$OVH_APP_DIR' && $cmd" \
    || die "remote command failed: $cmd"
}

open_tunnel() {
  if [ "$SKIP_TUNNEL" = "1" ]; then
    log "IMPORT_TOOL_SKIP_TUNNEL=1 — skipping SSH tunnel, using DB_HOST/DB_PORT as-is"
    return 0
  fi

  log "Opening SSH tunnel 127.0.0.1:${TUNNEL_LOCAL_PORT} -> ${OVH_HOST}:3306"
  ssh "${SSH_OPTS_BASE[@]}" -i "$SSH_KEY" -f -N \
    -L "${TUNNEL_LOCAL_PORT}:127.0.0.1:3306" \
    -o ExitOnForwardFailure=yes \
    "${OVH_USER}@${OVH_HOST}" \
    || die "failed to establish SSH tunnel to ${OVH_HOST}"

  TUNNEL_PID="$(pgrep -f "L ${TUNNEL_LOCAL_PORT}:127.0.0.1:3306" | head -n1 || true)"

  # -f only confirms the forward was requested successfully, not that it's
  # actually accepting connections yet — probe it before trusting it.
  local attempt
  # shellcheck disable=SC2034  # loop counter, not read in the body
  for attempt in $(seq 1 10); do
    if (exec 3<>"/dev/tcp/127.0.0.1/${TUNNEL_LOCAL_PORT}") 2>/dev/null; then
      exec 3>&- 3<&- 2>/dev/null || true
      log "Tunnel verified reachable on 127.0.0.1:${TUNNEL_LOCAL_PORT}"
      return 0
    fi
    sleep 1
  done
  die "tunnel did not become reachable on 127.0.0.1:${TUNNEL_LOCAL_PORT} after 10s"
}

close_tunnel() {
  if [ -n "$TUNNEL_PID" ]; then
    kill "$TUNNEL_PID" 2>/dev/null || true
    TUNNEL_PID=""
  fi
}
trap close_tunnel EXIT

# ------------------------------------------------------------------------------
# Importer steps (run locally in the container against DB_HOST/DB_PORT, which
# the caller must set to point at the tunnel — see README.md)
# ------------------------------------------------------------------------------
# The importer's `import` command exits 1 whenever totals.errors > 0 (see
# src/cli/import.ts) — which this legacy dataset always has some of:
# individual rows with genuinely bad/missing legacy data, already tracked and
# summarized as warnings/errors in the run's own output, not a sign the whole
# run failed. Every mode (append/clean/stage) needs image-sync/glossary-resync
# to still run against whatever WAS imported rather than aborting the entire
# pipeline over a handful of known, already-summarized per-row failures — this
# used to die() here, which silently skipped image-sync and glossary:resync on
# every real append/clean run (confirmed: the OVH `clean` run that produced
# "135343 imported ... 385 errors" never reached either step, because this
# function killed the container right after the import summary printed).
run_importer_import() {
  local extra_args=()
  [ "$DRY_RUN" = "1" ] && extra_args+=(--dry-run)

  log "Running importer: import ${extra_args[*]}"
  local rc=0
  (cd "$IMPORTER_DIR" && npx tsx src/cli/import.ts import "${extra_args[@]}") || rc=$?
  if [ "$rc" -ne 0 ]; then
    log "NOTE: importer 'import' exited non-zero (rc=$rc) — see the run's own summary above for the error count. Continuing to image-sync/glossary-resync regardless; this is expected for this dataset, not treated as fatal."
  fi
  return 0
}

run_image_sync_and_push() {
  local extra_args=()
  [ "$DRY_RUN" = "1" ] && extra_args+=(--dry-run)

  log "Running importer: image-sync --copy --target-dir $STAGING_DIR ${extra_args[*]}"
  mkdir -p "$STAGING_DIR"
  # Same reasoning as run_importer_import: image-sync exits 1 whenever any
  # individual file failed (e.g. a legacy image path that doesn't exist on
  # disk — this dataset always has a few hundred of these). Previously this
  # returned early and skipped the rsync push entirely, discarding every
  # image that WAS successfully staged, not just the ones that failed. Push
  # whatever landed in $STAGING_DIR regardless; the run's own summary above
  # already lists exactly what's missing.
  local sync_rc=0
  (cd "$IMPORTER_DIR" && npx tsx src/cli/import.ts image-sync --copy --target-dir "$STAGING_DIR" "${extra_args[@]}") || sync_rc=$?
  if [ "$sync_rc" -ne 0 ]; then
    log "NOTE: image-sync exited non-zero (rc=$sync_rc) — see the run's own summary above for what's missing. Pushing whatever was staged regardless."
  fi

  if [ "$DRY_RUN" = "1" ]; then
    log "DRY_RUN=1 — skipping rsync push to OVH"
    return 0
  fi

  local rsync_flags=(-az --stats)
  # --delete is only correct in "clean" mode: image-sync only stages NEWLY
  # synced images (it skips any row whose size != 1, i.e. already synced), so
  # in "append" mode the staging dir is a partial set, not a full mirror —
  # --delete there would erase every image already on OVH from a prior run.
  # In "clean" mode every row starts at size=1 after the fresh wipe, so the
  # staging dir genuinely is a full mirror and --delete correctly prunes
  # anything stale.
  if [ "$MODE" = "clean" ]; then
    rsync_flags+=(--delete)
  fi

  log "rsync ${rsync_flags[*]} $STAGING_DIR/ -> ${OVH_HOST}:${OVH_SHARED_DIR}/storage/app/public/pictures/"
  if ! rsync "${rsync_flags[@]}" -e "ssh ${SSH_OPTS_BASE[*]} -i $SSH_KEY" \
    "$STAGING_DIR"/ \
    "${OVH_USER}@${OVH_HOST}:${OVH_SHARED_DIR}/storage/app/public/pictures/"; then
    log "rsync push failed"
    return 1
  fi
}

run_image_sync_local_only() {
  local extra_args=()
  [ "$DRY_RUN" = "1" ] && extra_args+=(--dry-run)

  log "Running importer: image-sync --copy --target-dir $STAGING_DIR ${extra_args[*]} (local only, no OVH push)"
  mkdir -p "$STAGING_DIR"
  local rc=0
  (cd "$IMPORTER_DIR" && npx tsx src/cli/import.ts image-sync --copy --target-dir "$STAGING_DIR" "${extra_args[@]}") || rc=$?
  if [ "$rc" -ne 0 ]; then
    # Same reasoning as run_importer_import: image-sync exits 1 whenever any
    # file failed (e.g. "Legacy image not found" — broken links already
    # present in the legacy image tree itself, not something this container
    # can fix). Not treated as fatal here; the run's own summary above
    # already lists exactly what's missing.
    log "NOTE: image-sync exited non-zero (rc=$rc) — see the run's own summary above for what's missing. Not treated as fatal in stage mode."
  fi
  return 0
}

run_glossary_resync() {
  if [ "$DRY_RUN" = "1" ]; then
    log "DRY_RUN=1 — skipping glossary:resync"
    return 0
  fi
  log "Queuing glossary resync (inventory-queue.service consumes it; not waiting for drain)"
  ssh_run "php artisan glossary:resync --remove-existing --force"
}

# ------------------------------------------------------------------------------
# Mode implementations
# ------------------------------------------------------------------------------
do_import_pipeline() {
  prepare_ssh_key
  open_tunnel
  run_importer_import

  local pid_a pid_b rc_a=0 rc_b=0
  run_image_sync_and_push &
  pid_a=$!
  run_glossary_resync &
  pid_b=$!
  wait "$pid_a" || rc_a=$?
  wait "$pid_b" || rc_b=$?
  close_tunnel

  [ "$rc_a" -eq 0 ] || die "image-sync/rsync branch failed (exit $rc_a)"
  [ "$rc_b" -eq 0 ] || die "glossary:resync branch failed (exit $rc_b)"
}

# True if a snapshot file already exists on the OVH host. Checked with a
# plain remote `test -f` rather than parsing auth:restore's error text —
# more robust, and lets us distinguish "no snapshot, fall back" from any
# OTHER auth:restore failure (wrong APP_KEY, missing roles, etc.), which
# should still abort the pipeline hard rather than silently paper over it.
snapshot_exists() {
  ssh "${SSH_OPTS_BASE[@]}" -i "$SSH_KEY" "${OVH_USER}@${OVH_HOST}" \
    "test -f '$AUTH_SNAPSHOT_REMOTE'"
}

# Recreates exactly two accounts (one "Manager of Users" admin, one
# "Regular User") when no snapshot exists to restore instead. Requires
# ADMIN_EMAIL/REGULAR_USER_EMAIL — never invented or defaulted. user:create
# does not set a plaintext password; it emails a password-reset invitation
# to that address.
create_fallback_accounts() {
  log "No snapshot to restore — recreating fallback accounts for ADMIN_EMAIL and REGULAR_USER_EMAIL instead"
  ssh_run "php artisan user:create '$ADMIN_EMAIL' '$ADMIN_EMAIL'"
  ssh_run "php artisan user:email-verification '$ADMIN_EMAIL' verify"
  ssh_run "php artisan user:assign-role '$ADMIN_EMAIL' 'Manager of Users'"
  ssh_run "php artisan user:create '$REGULAR_USER_EMAIL' '$REGULAR_USER_EMAIL'"
  ssh_run "php artisan user:email-verification '$REGULAR_USER_EMAIL' verify"
  ssh_run "php artisan user:assign-role '$REGULAR_USER_EMAIL' 'Regular User'"
  log "Fallback accounts created — check $ADMIN_EMAIL and $REGULAR_USER_EMAIL for password-reset emails"
}

do_wipe_and_restore() {
  [ "${CONFIRM_WIPE:-}" = "yes-really-wipe-production" ] \
    || die "clean mode requires CONFIRM_WIPE=yes-really-wipe-production — refusing to wipe the database"

  prepare_ssh_key
  ssh_run "php artisan db:wipe --force"
  ssh_run "php artisan migrate --force"
  ssh_run "php artisan optimize:clear"
  ssh_run "php artisan db:seed --class=MinimalDatabaseSeeder --force"
  ssh_run "php artisan permission:sync"

  if snapshot_exists; then
    ssh_run "php artisan auth:restore '$AUTH_SNAPSHOT_REMOTE' --force"
  elif [ -n "$ADMIN_EMAIL" ] && [ -n "$REGULAR_USER_EMAIL" ]; then
    create_fallback_accounts
  else
    die "no snapshot at $AUTH_SNAPSHOT_REMOTE and ADMIN_EMAIL/REGULAR_USER_EMAIL not both set — nothing to restore and no fallback configured. Run backup-permissions first, or set both in .env to allow account recreation as a fallback."
  fi
}

do_stage() {
  log "Stage mode: writing straight to DB_HOST=${DB_HOST:-<unset>} — no SSH, no OVH contact"
  run_importer_import
  run_image_sync_local_only
}

# Dumps LOCAL_DB_* (local-mysql, built by a prior `stage` run), excluding
# every table in SHIP_EXCLUDED_TABLES, and loads it through the already-open
# OVH tunnel (DB_HOST/DB_PORT/etc., set up by do_wipe_and_restore + open_tunnel
# before this runs).
#
# The load leg deliberately does NOT pipe mysqldump's output straight into
# this image's own `mysql` CLI: that CLI is Alpine's MariaDB client, which
# can't authenticate to a modern MySQL 8 server's default
# caching_sha2_password plugin at all (confirmed: "Plugin caching_sha2_password
# could not be loaded" — the plugin's .so isn't present in this package, not
# an SSL configuration issue) — and that's exactly what both local-mysql and
# the real OVH target use. Instead: dump to a temp file with mysqldump
# (--ssl=0 needed even for the dump leg — local-mysql's self-signed cert isn't
# trusted by this client either), then load that file via the importer's own
# `load-sql` command, which uses the same mysql2 driver already proven to talk
# to OVH throughout every other mode in this tool.
load_staged_dump() {
  local ignore_flags=()
  local t
  for t in "${SHIP_EXCLUDED_TABLES[@]}"; do
    ignore_flags+=(--ignore-table="${LOCAL_DB_DATABASE}.${t}")
  done

  local dump_file="/tmp/staged-dump.sql"
  log "Dumping local-mysql (excluding: ${SHIP_EXCLUDED_TABLES[*]}) to $dump_file"
  mysqldump -h "$LOCAL_DB_HOST" -P "$LOCAL_DB_PORT" -u "$LOCAL_DB_USERNAME" -p"$LOCAL_DB_PASSWORD" --ssl=0 \
    --single-transaction --routines --no-tablespaces "${ignore_flags[@]}" "$LOCAL_DB_DATABASE" \
    > "$dump_file" \
    || die "dumping local-mysql failed"

  log "Loading $dump_file into ${DB_DATABASE}@${DB_HOST}:${DB_PORT} (through the tunnel)"
  (cd "$IMPORTER_DIR" && npx tsx src/cli/import.ts load-sql --file "$dump_file") \
    || die "loading staged dump into ${DB_DATABASE} failed"

  rm -f "$dump_file"
}

# Pushes images already staged by a prior `stage` run (local-images-data,
# mounted at $STAGING_DIR) to OVH. Unlike run_image_sync_and_push, this never
# re-runs the importer's image-sync step — the images are already there.
# --delete is always correct here: do_wipe_and_restore already wiped OVH, so
# whatever is in $STAGING_DIR is the full, authoritative set.
push_staged_images() {
  if [ "$DRY_RUN" = "1" ]; then
    log "DRY_RUN=1 — skipping rsync push to OVH"
    return 0
  fi
  log "rsync -az --stats --delete $STAGING_DIR/ -> ${OVH_HOST}:${OVH_SHARED_DIR}/storage/app/public/pictures/"
  rsync -az --stats --delete -e "ssh ${SSH_OPTS_BASE[*]} -i $SSH_KEY" \
    "$STAGING_DIR"/ \
    "${OVH_USER}@${OVH_HOST}:${OVH_SHARED_DIR}/storage/app/public/pictures/" \
    || die "rsync push failed"
}

# Ships an already-built `stage` copy to OVH. The app layer (users, roles,
# permissions, tokens) is always rebuilt fresh on OVH itself by
# do_wipe_and_restore — reusing it here unchanged means `ship` restores real
# users/roles from OVH's own snapshot (or the fallback accounts) exactly like
# `clean` does, and never reads any of that from local-mysql (which has none
# of it — see do_stage). Only the CONTENT tables (the actual imported legacy
# data) come from the local build.
do_ship() {
  do_wipe_and_restore

  open_tunnel
  load_staged_dump
  close_tunnel

  push_staged_images
  run_glossary_resync
}

do_backup_permissions() {
  prepare_ssh_key
  mkdir -p "$AUTH_SNAPSHOT_LOCAL_BACKUP_DIR"
  local ts
  ts="$(date -u +%Y%m%dT%H%M%SZ)"

  ssh_run "php artisan auth:snapshot '$AUTH_SNAPSHOT_REMOTE' --force"

  local local_copy="${AUTH_SNAPSHOT_LOCAL_BACKUP_DIR}/auth-snapshot-${ts}.json.enc"
  log "Pulling redundant local copy to $local_copy"
  scp "${SSH_OPTS_BASE[@]}" -i "$SSH_KEY" \
    "${OVH_USER}@${OVH_HOST}:${AUTH_SNAPSHOT_REMOTE}" \
    "$local_copy" \
    || die "failed to pull local backup copy of snapshot"
}

# ------------------------------------------------------------------------------
# Dispatch
# ------------------------------------------------------------------------------
case "$MODE" in
append)
  require_ovh_host
  do_import_pipeline
  ;;
backup-permissions)
  require_ovh_host
  do_backup_permissions
  ;;
clean)
  require_ovh_host
  do_wipe_and_restore
  do_import_pipeline
  ;;
stage)
  do_stage
  ;;
ship)
  require_ovh_host
  [ "${CONFIRM_WIPE:-}" = "yes-really-wipe-production" ] \
    || die "ship mode requires CONFIRM_WIPE=yes-really-wipe-production — refusing to wipe the database"
  do_ship
  ;;
*)
  die "unknown mode '$MODE' (expected: append | backup-permissions | clean | stage | ship)"
  ;;
esac

log "Mode '$MODE' completed successfully"
