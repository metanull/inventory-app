#!/bin/sh
#
# check-dependabot-coverage.sh
#
# Fails when the repository's Node projects and .github/dependabot.yml disagree.
#
# .github/dependabot.yml is maintained BY HAND and cannot be otherwise:
# Dependabot config is static YAML with no scripting, so unlike the
# `Exporter Validation` matrix in continuous-integration.yml and the
# `enumerate-npm-projects` job in dependency-audit.yml — both of which glob the
# tree at runtime — it cannot enumerate scripts/exporters/* and
# scripts/viewers/*. Generating the file was considered and rejected.
#
# Documentation alone already failed once: scripts/viewers/amulets shipped in
# PR #1566 with no Dependabot entry, roughly an hour after PR #1557 wrote the
# rule down, and had to be patched in PR #1572. A Node project missing from
# that file receives no dependency updates and no security alerts, silently and
# indefinitely. This script is what makes forgetting fail loudly.
#
# Three assertions:
#   1. Every package.json under scripts/** (plus the root and spa) has a
#      matching `npm` entry with the right `directory:`.
#   2. Every `npm` entry's `directory:` resolves to a package.json that exists,
#      so a deleted or renamed project is caught too.
#   3. Every viewer entry carries `registries: [npm-github]` (viewers install
#      @metanull/<dataset>-data from GitHub Packages) and no exporter entry
#      does (exporters read the database and write JSON; they consume no
#      @metanull package).
#
# Usage (from the repository root):
#
#   sh scripts/check-dependabot-coverage.sh
#
# There is no host-side tooling in this project, so run it in a container:
#
#   docker run --rm -v "$PWD:/repo" -w /repo --entrypoint sh mikefarah/yq:4 \
#     scripts/check-dependabot-coverage.sh
#
# Requires: yq (mikefarah v4) and POSIX sh. Both are present on the
# ubuntu-latest GitHub runner, where the `dependabot-coverage` job in
# continuous-integration.yml runs this same script.

set -eu

# comm(1) needs both inputs sorted under the same collation as sort(1).
LC_ALL=C
export LC_ALL

CONFIG=".github/dependabot.yml"

if [ ! -f "$CONFIG" ]; then
  echo "ERROR: $CONFIG not found. Run this script from the repository root." >&2
  exit 1
fi

if ! command -v yq >/dev/null 2>&1; then
  cat >&2 <<'EOF'
ERROR: `yq` (mikefarah v4) is required and was not found on PATH.

This project has no host-side tooling; run the check in a container instead:

  docker run --rm -v "$PWD:/repo" -w /repo --entrypoint sh mikefarah/yq:4 \
    scripts/check-dependabot-coverage.sh
EOF
  exit 1
fi

# --------------------------------------------------------------------------
# 1. Enumerate the Node projects that exist in the tree.
#
# Directories are emitted in Dependabot's own `directory:` form: an absolute
# path from the repository root, "/" for the root project itself.
# --------------------------------------------------------------------------

TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

FOUND="$TMP/found"
CONFIGURED="$TMP/configured"

{
  [ -f package.json ] && echo "/"
  [ -f spa/package.json ] && echo "/spa"
  find scripts -name node_modules -prune -o -name package.json -print 2>/dev/null \
    | sed 's|/package\.json$||; s|^|/|'
} | sort -u > "$FOUND"

# --------------------------------------------------------------------------
# 2. Read the npm entries out of the Dependabot config.
#
# One line per entry: "<directory>|<comma-separated registries>".
# --------------------------------------------------------------------------

yq -r '
  .updates[]
  | select(.["package-ecosystem"] == "npm")
  | .directory + "|" + ((.registries // []) | join(","))
' "$CONFIG" | sed 's#\(.\)/|#\1|#' | sort > "$CONFIGURED"

CONFIGURED_DIRS="$TMP/configured-dirs"
cut -d'|' -f1 "$CONFIGURED" | sort > "$CONFIGURED_DIRS"

# --------------------------------------------------------------------------
# Helper: emit a ready-to-paste YAML block for a directory.
# --------------------------------------------------------------------------

group_prefix() {
  case "$1" in
    /) echo "node" ;;
    /spa) echo "spa" ;;
    /scripts/exporters/*) echo "exporter-$(basename "$1")" ;;
    /scripts/viewers/*) echo "viewer-$(basename "$1")" ;;
    *) basename "$1" ;;
  esac
}

needs_registry() {
  case "$1" in
    /scripts/viewers/*) return 0 ;;
    *) return 1 ;;
  esac
}

section_hint() {
  case "$1" in
    /scripts/exporters/*) echo 'the "Dataset exporters" section' ;;
    /scripts/viewers/*) echo 'the "Dataset viewers" section' ;;
    *) echo 'the npm entries, before the "Dataset exporters" section' ;;
  esac
}

suggest_block() {
  dir="$1"
  prefix="$(group_prefix "$dir")"

  echo "    Paste this into $CONFIG, under $(section_hint "$dir"):"
  echo
  echo "      - package-ecosystem: \"npm\""
  echo "        directory: \"$dir\""
  echo "        schedule:"
  echo "          interval: \"weekly\""
  echo "        labels:"
  echo "          - \"npm\""
  echo "          - \"dependencies\""
  echo "        groups:"
  echo "          $prefix-dev-dependencies:"
  echo "            dependency-type: \"development\""
  echo "          $prefix-prod-dependencies:"
  echo "            dependency-type: \"production\""
  if needs_registry "$dir"; then
    echo "        registries:"
    echo "          - npm-github"
  fi
  echo
}

# --------------------------------------------------------------------------
# 3. Compare.
# --------------------------------------------------------------------------

fail=0

echo "==> Dependabot coverage"
echo "    Node projects in the tree     : $(wc -l < "$FOUND" | tr -d ' ')"
echo "    npm entries in $CONFIG : $(wc -l < "$CONFIGURED" | tr -d ' ')"
echo

MISSING="$(comm -23 "$FOUND" "$CONFIGURED_DIRS")"
ORPHANED="$(comm -13 "$FOUND" "$CONFIGURED_DIRS")"
DUPLICATED="$(uniq -d "$CONFIGURED_DIRS")"

if [ -n "$MISSING" ]; then
  fail=1
  echo "MISSING — these Node projects have no npm entry in $CONFIG:"
  echo
  echo "$MISSING" | while IFS= read -r dir; do
    [ -n "$dir" ] || continue
    echo "  $dir"
    echo
    suggest_block "$dir"
  done
fi

if [ -n "$ORPHANED" ]; then
  fail=1
  echo "ORPHANED — these npm entries in $CONFIG point at a directory with no package.json:"
  echo
  echo "$ORPHANED" | while IFS= read -r dir; do
    [ -n "$dir" ] || continue
    echo "  $dir"
  done
  echo
  echo "    The project was deleted or renamed. Remove the entry, or correct its"
  echo "    directory: to the new path."
  echo
fi

if [ -n "$DUPLICATED" ]; then
  fail=1
  echo "DUPLICATED — these directories have more than one npm entry in $CONFIG:"
  echo
  echo "$DUPLICATED" | while IFS= read -r dir; do
    [ -n "$dir" ] || continue
    echo "  $dir"
  done
  echo
fi

# --------------------------------------------------------------------------
# 4. The registry rule.
# --------------------------------------------------------------------------

REGISTRY_ERRORS="$TMP/registry-errors"
: > "$REGISTRY_ERRORS"

while IFS='|' read -r dir registries; do
  [ -n "$dir" ] || continue
  case "$dir" in
    /scripts/viewers/*)
      case ",$registries," in
        *,npm-github,*) ;;
        *)
          {
            echo "  $dir"
            echo "      has registries: [${registries:-<none>}]"
            echo "      needs npm-github — a viewer installs @metanull/$(basename "$dir")-data"
            echo "      from GitHub Packages, which requires authentication."
            echo "      Add to that entry:"
            echo
            echo "        registries:"
            echo "          - npm-github"
            echo
          } >> "$REGISTRY_ERRORS"
          ;;
      esac
      ;;
    /scripts/exporters/*)
      if [ -n "$registries" ]; then
        {
          echo "  $dir"
          echo "      has registries: [$registries]"
          echo "      must have none — an exporter reads the database and writes JSON."
          echo "      It consumes no @metanull package, so it uses the public registry."
          echo "      Delete the registries: key from that entry."
          echo
        } >> "$REGISTRY_ERRORS"
      fi
      ;;
  esac
done < "$CONFIGURED"

if [ -s "$REGISTRY_ERRORS" ]; then
  fail=1
  echo "REGISTRY RULE — these npm entries name the wrong registries:"
  echo
  cat "$REGISTRY_ERRORS"
fi

# --------------------------------------------------------------------------
# 5. Verdict.
# --------------------------------------------------------------------------

if [ "$fail" = "1" ]; then
  cat <<EOF
--------------------------------------------------------------------------
Why isn't $CONFIG generated instead?

Dependabot reads this file as static YAML from the default branch. It has no
templating, no includes and no scripting, so it cannot glob scripts/exporters/*
and scripts/viewers/* the way the CI matrices do. Generating and committing it
would put a build artefact in the tree and leave the same "did you re-run the
generator?" gap it was meant to close. That option was considered and rejected;
the file is written by hand, and this check is what keeps it honest.

Fix $CONFIG, not this script. Then re-run:

  sh scripts/check-dependabot-coverage.sh

See .github/workflows/README.md#dependabot-configuration for the full policy.
--------------------------------------------------------------------------
EOF
  exit 1
fi

echo "OK — every Node project has an npm entry, every npm entry has a project,"
echo "     and every entry names the right registries."
