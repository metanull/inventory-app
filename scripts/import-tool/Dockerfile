# Dedicated tool image for running the legacy-data import pipeline against
# the OVH-hosted inventory-app over an SSH tunnel.
#
# Build from the REPO ROOT (not this directory), since it needs to copy
# scripts/importer:
#   docker build -f scripts/import-tool/Dockerfile -t import-tool:latest .

FROM node:22-alpine

RUN apk add --no-cache openssh-client rsync bash

WORKDIR /opt/import-tool/importer

# Install importer dependencies first (better layer caching on source changes).
# NOT --omit=dev: tsx (the TypeScript runner this CLI is invoked through) is
# a devDependency but genuinely needed at runtime here — omitting it forces
# `npx tsx` to silently fetch an unpinned copy from the registry on every run.
COPY scripts/importer/package.json scripts/importer/package-lock.json ./
RUN npm ci

# Importer source. tsx runs TypeScript directly — no build step.
COPY scripts/importer/tsconfig.json ./
COPY scripts/importer/src ./src

COPY scripts/import-tool/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["append"]
