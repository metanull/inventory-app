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
#                       Requires a snapshot already at $AUTH_SNAPSHOT_REMOTE
#                       (run backup-permissions first) and requires
#                       CONFIRM_WIPE=yes-really-wipe-production.
#
# See README.md for the full list of required env vars and mounts.
# ==============================================================================

IMPORTER_DIR=/opt/import-tool/importer

MODE="${1:-${IMPORT_MODE:-append}}"

OVH_HOST="${OVH_HOST:?OVH_HOST is required}"
OVH_USER="${OVH_USER:-deploy}"
OVH_APP_DIR="${OVH_APP_DIR:-/opt/inventory/current}"
OVH_SHARED_DIR="${OVH_SHARED_DIR:-/opt/inventory/shared}"
OVH_SSH_KEY_PATH="${OVH_SSH_KEY_PATH:-/run/secrets/deploy_key}"

TUNNEL_LOCAL_PORT="${TUNNEL_LOCAL_PORT:-3307}"
STAGING_DIR="${IMAGE_STAGING_DIR:-/staging/images}"

AUTH_SNAPSHOT_REMOTE="${AUTH_SNAPSHOT_REMOTE:-${OVH_SHARED_DIR}/auth-snapshots/current.json.enc}"
AUTH_SNAPSHOT_LOCAL_BACKUP_DIR="${AUTH_SNAPSHOT_LOCAL_BACKUP_DIR:-/backup}"

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
run_importer_import() {
  local extra_args=()
  [ "$DRY_RUN" = "1" ] && extra_args+=(--dry-run)

  log "Running importer: import ${extra_args[*]}"
  (cd "$IMPORTER_DIR" && npx tsx src/cli/import.ts import "${extra_args[@]}") \
    || die "importer 'import' step failed"
}

run_image_sync_and_push() {
  local extra_args=()
  [ "$DRY_RUN" = "1" ] && extra_args+=(--dry-run)

  log "Running importer: image-sync --copy --target-dir $STAGING_DIR ${extra_args[*]}"
  mkdir -p "$STAGING_DIR"
  if ! (cd "$IMPORTER_DIR" && npx tsx src/cli/import.ts image-sync --copy --target-dir "$STAGING_DIR" "${extra_args[@]}"); then
    log "image-sync failed"
    return 1
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

do_wipe_and_restore() {
  [ "${CONFIRM_WIPE:-}" = "yes-really-wipe-production" ] \
    || die "clean mode requires CONFIRM_WIPE=yes-really-wipe-production — refusing to wipe the database"

  prepare_ssh_key
  ssh_run "php artisan db:wipe --force"
  ssh_run "php artisan migrate --force"
  ssh_run "php artisan optimize:clear"
  ssh_run "php artisan db:seed --class=MinimalDatabaseSeeder --force"
  ssh_run "php artisan permission:sync"
  # Requires a snapshot already at $AUTH_SNAPSHOT_REMOTE (from a prior
  # backup-permissions run). If none exists, auth:restore fails cleanly with
  # "snapshot not found" and this aborts the whole pipeline before anything
  # further happens — the correct, safe failure mode.
  ssh_run "php artisan auth:restore '$AUTH_SNAPSHOT_REMOTE' --force"
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
  do_import_pipeline
  ;;
backup-permissions)
  do_backup_permissions
  ;;
clean)
  do_wipe_and_restore
  do_import_pipeline
  ;;
*)
  die "unknown mode '$MODE' (expected: append | backup-permissions | clean)"
  ;;
esac

log "Mode '$MODE' completed successfully"
