---
description: "Use when: modifying the Build workflow (build.yml), changing artifact packaging, adjusting release creation, debugging deployment package contents, or understanding how build artifacts flow to the Windows (deploy.yml) and OVH (deploy-ovh.yml) deployment pipelines."
applyTo: ".github/workflows/build.yml"
---

# Build Workflow — Artifact Packaging & Release Creation

The `build.yml` workflow builds the application on `ubuntu-latest` and produces **two distinct artifacts** for the two deployment targets.

## Triggers

| Trigger | Condition |
|---------|-----------|
| `push` to `main` | Automatic on every merge |
| `push` tag `v*.*.*` | Automatic on version tags |
| `workflow_dispatch` | Manual |

## Build Steps

1. Checkout code
2. Setup PHP 8.4 + Node.js (LTS/Krypton)
3. `composer install --no-dev` (production dependencies only)
4. `npm ci` + `npm run build` (Blade/Vite frontend assets → `public/build/`)
5. SPA demo: `npm ci` + `npm run build` (Vue app → `public/cli/`)
6. Validate asset paths exist (`public/build`, `public/cli`)
7. Create deployment package (selective copy, not full repo)
8. Generate `VERSION` file (JSON with version, commit, build number)
9. Create **two archives** from the same deployment directory
10. Create GitHub Release with ZIP attached
11. Upload tarball as workflow artifact

## Deployment Package Contents

The package is a **curated subset** of the repository — not a full clone. It contains only production-necessary files:

### Included directories

| Directory | Purpose |
|-----------|---------|
| `app/` | Laravel application code |
| `bootstrap/` | Framework bootstrap |
| `config/` | Configuration files |
| `database/` | Migrations, factories, seeders |
| `public/` | Web root (includes `build/` and `cli/` compiled assets) |
| `resources/` | Blade templates, CSS, JS sources |
| `routes/` | Route definitions |
| `vendor/` | Production Composer dependencies (no dev) |
| `storage/` | Directory structure only (empty — content is shared/persistent) |

### Included files

`composer.json`, `composer.lock`, `artisan`, `.env.example`, `package.json`, `package-lock.json`

### Excluded (NOT in the package)

`tests/`, `scripts/`, `spa/src/`, `docs/`, `node_modules/`, `.github/`, dev dependencies, IDE config

### Generated files

| File | Location | Content |
|------|----------|---------|
| `VERSION` | Root | JSON: `app_version`, `api_client_version`, `commit_sha`, `build_timestamp`, `build_number`, `unique_build_id`, `repository`, `repository_url`, `branch` |
| `version.json` | `public/` | Copy of `VERSION` (accessible via HTTP for SPA/monitoring) |

## Two Artifacts, Two Consumers

The build produces **two archives from the same deployment directory** with different formats for different consumers:

### 1. ZIP → GitHub Release → Windows deployment (`deploy.yml`)

| Property | Value |
|----------|-------|
| Format | `inventory-app.zip` |
| Storage | Attached to a GitHub Release (tag: `<version>.<run_number>`) |
| Retention | Permanent (GitHub Releases) |
| Consumer | `deploy.yml` (self-hosted Windows runner) |
| How consumed | Runner downloads ZIP via GitHub API using release tag |
| Release type | `prerelease: true` |

The Windows deployment workflow (`deploy.yml`) is triggered manually via `workflow_dispatch` with a release tag input. The self-hosted runner downloads the ZIP from the GitHub Release API, extracts it, and deploys using the blue-green symlink pattern.

### 2. Tarball → Workflow Artifact → OVH deployment (`deploy-ovh.yml`)

| Property | Value |
|----------|-------|
| Format | `release.tar.gz` |
| Artifact name | `release-<commit-sha>` |
| Storage | GitHub Actions artifact (via `actions/upload-artifact@v7`) |
| Retention | 7 days |
| Consumer | `deploy-ovh.yml` (triggered automatically by `workflow_run`) |
| How consumed | `actions/download-artifact@v8` with `run-id` from the Build run |

The OVH deployment workflow (`deploy-ovh.yml`) is triggered automatically when a Build run succeeds on `main`. It downloads the tarball artifact, SCPs it to the VPS, and runs `deploy-ovh.sh` via SSH.

## Version & Tag Scheme

```
Tag:        <APP_VERSION>.<GITHUB_RUN_NUMBER>
Example:    1.2.3.42
```

- `APP_VERSION` is read from `package.json` → `version` field
- `GITHUB_RUN_NUMBER` is appended as the build number
- The tag is created as a `prerelease` GitHub Release

## Flow Diagram

```
build.yml (ubuntu-latest)
    │
    ├── inventory-app.zip ──► GitHub Release (permanent)
    │                              │
    │                              ▼
    │                         deploy.yml (manual trigger, self-hosted Windows runner)
    │                         → Blue-green symlink on MWNF server
    │
    └── release.tar.gz ────► Workflow Artifact (7-day retention)
                                   │
                                   ▼
                              deploy-ovh.yml (auto-trigger on success)
                              → SCP + deploy-ovh.sh on OVH VPS
```

## Forbidden

- **NEVER include dev dependencies** (`--no-dev` for Composer, no `devDependencies` in the package).
- **NEVER include test files, scripts, or docs** in the deployment package — they belong in the repo only.
- **NEVER change the artifact naming convention** (`release-<sha>` for OVH, `inventory-app.zip` for Windows) without updating both consumer workflows.
- **NEVER remove `VERSION`** or `public/version.json` — they are used for deployment traceability.
- **NEVER change the tarball retention** below 7 days — OVH deploy may run later.
- **NEVER use `draft: true`** on releases — the Windows deploy workflow fetches by tag, not by draft status.
