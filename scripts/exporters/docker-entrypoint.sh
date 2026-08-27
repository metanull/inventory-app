#!/bin/sh
#
# Runs one dataset exporter inside the compose `exporter` service.
#
#   docker compose run --rm exporter islamicart
#   docker compose run --rm exporter baroqueart --force
#
# Everything after the dataset name is passed through to `npm run export`.
set -e

EXPORTERS_DIR="$(cd "$(dirname "$0")" && pwd)"

usage() {
    echo "usage: exporter <dataset> [export options...]" >&2
    echo "" >&2
    echo "available datasets:" >&2
    for d in "$EXPORTERS_DIR"/*/; do
        [ -f "${d}package.json" ] && echo "  $(basename "$d")" >&2
    done
    exit 64
}

DATASET="${1:-}"
[ -n "$DATASET" ] || usage
shift

TARGET="${EXPORTERS_DIR}/${DATASET}"
if [ ! -f "${TARGET}/package.json" ]; then
    echo "error: no exporter named '${DATASET}'" >&2
    usage
fi

cd "$TARGET"

# node_modules is a named volume, not the host directory: esbuild (via tsx)
# ships a platform-specific binary, and the tree installed on a Windows host is
# unusable here. Empty on first run, so install into it once.
if [ ! -x node_modules/.bin/tsx ]; then
    echo "==> installing ${DATASET} exporter dependencies (first run only)"
    npm ci --no-fund --no-audit
fi

echo "==> exporting ${DATASET} from ${DB_HOST:-localhost}/${DB_DATABASE:-inventory}"
exec npm run export -- "$@"
