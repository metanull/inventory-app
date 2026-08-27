# Workflows

The `/.github/workflows` directory contains GitHub Actions workflows for continuous integration, deployment, and automation tasks.

## Table of contents

- [Workflows](#workflows)
  - [Table of contents](#table-of-contents)
  - [Notes](#notes)
  - [Continuous Integration and Testing](#continuous-integration-and-testing)
    - [CI](#ci)
    - [Dependency Audit (Full Tree)](#dependency-audit-full-tree)
  - [Continuous Deployment](#continuous-deployment)
    - [Build](#build)
    - [Deploy to OVH](#deploy-to-ovh)
    - [Deploy Documentation to GitHub Pages](#deploy-documentation-to-github-pages)
    - [Publish API Client Package](#publish-api-client-package)
    - [Deploy Dataset Viewers to OVH](#deploy-dataset-viewers-to-ovh)
  - [Automation Workflows](#automation-workflows)
    - [Dependabot Configuration](#dependabot-configuration)
    - [Merge Dependabot PR](#merge-dependabot-pr)
  - [Composite Actions](#composite-actions)
  - [Workflow Dependencies](#workflow-dependencies)
  - [Contributing](#contributing)

## Notes

- **Every workflow runs on GitHub-hosted runners** (`ubuntu-latest`, `ubuntu-24.04`); no self-hosted runner is used any more
- **Python workflows** use Python 3.x on Ubuntu runners for documentation generation
- Most workflows use **concurrency groups** to prevent duplicate runs and conserve resources
- Workflows are triggered by push events, pull requests, schedules, other workflow runs (`workflow_run`), or manual dispatch (`workflow_dispatch`)
- Repeated setup steps are factored out into **composite actions** under [`.github/actions`](../actions) — see [Composite Actions](#composite-actions)
- Several workflows interact with scripts in [/scripts/README.md](../../scripts/README.md)

## Continuous Integration and Testing

### CI

Runs the pull request validation pipeline: an unconditional dependency review, plus lint/build/test jobs that are gated on which paths changed. Jobs are skipped when no relevant files were modified. Provides the `CI Success` status check required by branch protection rules.

**Workflow properties**

| Property | Value |
| --- | --- |
| **Workflow** | `continuous-integration.yml` |
| **Workflow name** | `CI` |
| **Trigger** | Pull requests to `main` branch (opened, synchronize, reopened) |
| **Manual trigger** | No — CI gates pull requests and has no meaning outside one (`dependency-review` only supports `pull_request`, and path detection needs a PR base SHA) |
| **Runner** | `ubuntu-latest` (GitHub-hosted) |
| **Concurrency** | Group: `ci-mandatory-${{ github.ref }}`, cancel-in-progress: `true` |

**Path groups and triggered jobs**

| Changed paths | `detect-changes` output | Jobs triggered |
| --- | --- | --- |
| `app/**`, `routes/**`, `config/**`, `database/**`, `tests/**`, `bootstrap/**`, `composer.json`, `composer.lock`, `phpunit.xml`, `artisan` | `backend` | `backend-lint`, `backend-tests` |
| `resources/css/**`, `resources/js/**`, `resources/views/**`, `vite.config.js`, `tailwind.config.js`, `postcss.config.js`, `package.json`, `package-lock.json`, `tsconfig.json`, `eslint.config.js` | `root-frontend` | `backend-rendered-frontend-validation` |
| `scripts/importer/**` | `importer` | `importer-validation` |
| `scripts/site-i18n/**` | `site-i18n` | `site-i18n-validation` |
| `scripts/exporters/**` | `exporters` | `exporter-validation` |
| `spa/**` | `spa` | `spa-frontend-validation` |

**Jobs**

1. **detect-changes** (*Detect Changed Paths*) - Classifies changed files using `git diff` against the PR base SHA
   - Checks out the repository with full Git history (`fetch-depth: 0`)
   - Emits outputs: `backend`, `root-frontend`, `spa`, `importer`, `site-i18n`, `exporters` (true/false)
   - Also emits `exporter-datasets`, a JSON array of every directory under `scripts/exporters/` holding a `package.json`, used as the `exporter-validation` matrix

2. **dependency-review** (*Dependency Review (PR)*) - Reviews dependency changes introduced by the pull request
   - Runs `actions/dependency-review-action` with `fail-on-severity: high`
   - Has no `needs` and no path gate — it always runs

3. **dependabot-coverage** (*Dependabot Coverage*) - Enforces that `.github/dependabot.yml` matches the tree
   - Has no `needs` and no path gate — it always runs, because a deleted or renamed project must be caught as surely as a new one, and the whole check is a YAML parse plus a glob
   - Runs `sh scripts/check-dependabot-coverage.sh` (POSIX shell, `yq` only; no build, no service container, no matrix)
   - Fails when a `package.json` under `scripts/**`, the root or `spa` has no `npm` entry; when an `npm` entry's `directory:` resolves to no `package.json`; or when a viewer entry lacks `registries: [npm-github]` or an exporter entry carries the key
   - The failure output names each offender and prints the exact YAML block to paste, plus why the file is hand-maintained rather than generated
   - See [Dependabot Configuration](#dependabot-configuration) and [/scripts/README.md](../../scripts/README.md#dependabot-coverage-check)

4. **backend-lint** *(when `backend=true`)* (*Backend Linting and Validation*) - Laravel backend linting
   - Uses the `setup-backend` composite action with `tools: pint` (PHP 8.5, Composer install, `.env` from `.env.local.example`, `php artisan migrate`)
   - Runs against SQLite in memory (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`)
   - Runs `composer check-platform-reqs`
   - Runs `./vendor/bin/pint --bail`

5. **backend-tests** *(when `backend=true`)* (*Backend Tests (`<suite>`)*) - Laravel test matrix
   - Uses the `setup-backend` composite action with `tools: phpunit, pest` and `coverage: xdebug`
   - Matrix: `Unit`, `Api`, `Web`, `Filament`, `Configuration`, `Console`, `Event`, `Integration`
   - `fail-fast: true` — stops remaining suites on first failure
   - Runs each suite with `php artisan test --testsuite=<suite> --coverage --parallel --no-ansi --stop-on-failure`

6. **backend-rendered-frontend-validation** *(when `root-frontend=true`)* (*Backend Rendered Frontend Validation (Blade/Tailwind)*) - Blade/Tailwind build
   - Uses the `setup-node-project` composite action at the repository root
   - Runs `npm run build`

7. **importer-validation** *(when `importer=true`)* (*Importer Validation (TypeScript)*) - Importer TypeScript build and tests
   - Uses the `setup-node-project` composite action with `working-directory: scripts/importer`
   - Runs `npm run build` (TypeScript compilation)
   - Runs `npm test` (Vitest unit tests)

8. **site-i18n-validation** *(when `site-i18n=true`)* (*Site i18n Validation (TypeScript)*) - Shared site i18n layer validation
   - Uses the `setup-node-project` composite action with `working-directory: scripts/site-i18n`
   - Runs `npm run lint:check`, `npm run build` and `npm test`
   - Every test in this suite is a pure function over legacy row shapes, so no database, VPN or credentials are involved

9. **exporter-validation** *(when `exporters=true`)* (*Exporter Validation (`<dataset>`)*) - Dataset exporter validation
   - Matrix comes from `detect-changes`'s `exporter-datasets` output, not a hardcoded list — a forked exporter is covered from its first pull request
   - `fail-fast: false` — every dataset is reported, even when one fails
   - Uses the `setup-node-project` composite action with `working-directory: scripts/exporters/<dataset>`
   - Runs `npm run type-check`, `npm run lint:check` and `npm test` (Vitest unit tests)
   - Every test in these suites is a pure function over legacy row shapes, so no database, VPN or credentials are involved

10. **spa-frontend-validation** *(when `spa=true`)* (*Frontend Validation - SPA (Vue 3)*) - SPA (Vue 3) validation
   - Uses the `setup-node-project` composite action with `working-directory: spa`, authenticated against GitHub Packages with `GITHUB_TOKEN`
   - Runs `npm run lint`, `npm run build` and `npm run test:all`

11. **ci-success** (*CI Success*) - Aggregates all check results
   - `needs` every other job and runs with `if: always()`
   - Always requires `dependency-review` and `dependabot-coverage` to have succeeded
   - For each path group that changed, requires the matching job(s) to have succeeded
   - When a path group did not change, its job may be skipped without failing the workflow
   - This job name satisfies the `CI Success` branch protection required check

**Permissions**

- `contents: read` - For reading repository contents
- `packages: read` - For accessing GitHub Packages (SPA dependencies)

**Branch protection**

The `CI Success` job in this workflow satisfies the `CI Success` required status check configured in branch protection rules for `main`. Dependency review and the Dependabot coverage check must always pass; the path-gated lint/build/test jobs must pass whenever their path group changed.

**Usage**

This workflow runs automatically on pull requests. Skipped jobs are expected when their path group has no changed files — a skipped job is a pass, and `ci-success` only requires a job whose path group actually changed.

There is no manual trigger: CI exists to gate a pull request, and both `dependency-review` and path detection are defined only in that context. To re-run it, push to the branch or use *Re-run all jobs* on the existing run.

---

### Dependency Audit (Full Tree)

Audits the full dependency tree of every PHP and npm project in the repository on a weekly schedule, and opens (or comments on) a tracking issue when vulnerabilities are found. Dependency *changes* on a pull request are handled by the `dependency-review` job of the `CI` workflow instead.

**Workflow properties**

| Property | Value |
| --- | --- |
| **Workflow** | `dependency-audit.yml` |
| **Workflow name** | `Dependency Audit (Full Tree)` |
| **Trigger** | Schedule — `0 6 * * 1` (every Monday at 06:00 UTC) |
| **Manual trigger** | Yes (`workflow_dispatch`) |
| **Runner** | `ubuntu-latest` (GitHub-hosted) |
| **Concurrency** | Group: `dependency-audit`, cancel-in-progress: `true` |

**Jobs**

1. **audit-composer** (*Audit - Composer (PHP)*) - Audits PHP/Composer dependencies
   - Installs PHP 8.5 with extensions
   - Installs Composer dependencies
   - Runs `composer audit`

2. **enumerate-npm-projects** (*Enumerate npm Projects*) - Builds the `audit-npm` matrix from the checkout
   - Emits `projects`, a JSON array of `{name, directory, registry}` objects
   - Datasets are added by forking an existing directory, so the trees are listed and the projects are globbed — a fork is audited from the day it lands, with no edit to this workflow

   | Source | Contributes | Registry |
   | --- | --- | --- |
   | listed explicitly | `Root` (`.`) | public npm |
   | listed explicitly | `SPA` (`spa`) | npm.pkg.github.com |
   | listed explicitly | `Importer` (`scripts/importer`) | public npm |
   | every `package.json` under `scripts/exporters/*/` | `Exporter (<dataset>)` | public npm |
   | every `package.json` under `scripts/viewers/*/` | `Viewer (<dataset>)` | npm.pkg.github.com |

3. **audit-npm** (*Audit - npm (`<name>`)*) - Audits every npm project, as a single matrix job
   - Matrix: `fromJSON` of `enumerate-npm-projects`'s `projects` output
   - `fail-fast: false` — every directory is audited even if one fails
   - Uses the `setup-node-project` composite action per matrix entry, with that entry's registry
   - Runs `npm audit --audit-level high` in each directory

4. **report** (*Report Failures*) - Reports vulnerabilities as an issue
   - `needs: [audit-composer, enumerate-npm-projects, audit-npm]`, runs with `if: always()` when any of them failed
   - Opens an issue titled `Weekly dependency audit found vulnerabilities` with the `dependencies` label, or comments on the existing open one

**Permissions**

- `contents: read` - For reading repository contents
- `packages: read` - For accessing GitHub Packages (SPA and viewer dependencies)
- `issues: write` - For opening or commenting on the tracking issue

**Usage**

This workflow runs automatically every Monday. For manual triggering:

```
Actions > Dependency Audit (Full Tree) > Run workflow
```

---

## Continuous Deployment

### Build

Builds the Laravel application and the SPA demo, packages them, publishes a GitHub pre-release, and uploads the deployment tarball consumed by `Deploy to OVH`.

**Workflow properties**

| Property | Value |
| --- | --- |
| **Workflow** | `build.yml` |
| **Workflow name** | `Build` |
| **Trigger** | Push to `main` branch, or push of a `v*.*.*` tag |
| **Manual trigger** | Yes (`workflow_dispatch`) |
| **Runner** | `ubuntu-latest` (GitHub-hosted) |
| **Environment** | `MWNF-SVR` |
| **Concurrency** | Group: `cd-${{ github.ref }}`, cancel-in-progress: `true` |

**Job: build** (*Build and Package Application*)

1. Installs PHP 8.5 (matching `.docker/Dockerfile` and the VPS) with Pint, and Node.js `lts/Krypton`
2. Installs PHP dependencies with `composer install --no-dev --optimize-autoloader`
3. Publishes Filament assets (`php artisan filament:assets`)
4. Installs and builds the backend assets with Vite (`npm ci`, `npm run build`)
5. Installs and builds the SPA demo (`spa/`), authenticated against GitHub Packages
6. Validates that `public/build`, `public/cli`, `public/css/filament` and `public/js/filament` exist
7. Creates the deployment package and a `VERSION` file (app version, API client version, commit SHA, build number, timestamps), also copied to `public/version.json`
8. Produces both `inventory-app.zip` and `release.tar.gz`
9. Generates the release tag `<package.json version>.<run_number>`
10. Creates a GitHub pre-release with `inventory-app.zip` attached
11. Uploads the artifact `release-${{ github.sha }}` (containing `release.tar.gz`) with 7-day retention

**Permissions**

- `contents: write` - For creating releases
- `packages: read` - For accessing GitHub Packages (SPA dependencies)

**Usage**

This workflow runs automatically when changes are pushed to `main`. It is the upstream of `Deploy to OVH`.

---

### Deploy to OVH

Deploys the tarball produced by `Build` to the OVH VPS over SSH, by running [`scripts/deploy.sh`](../../scripts/deploy.sh) on the server.

**Workflow properties**

| Property | Value |
| --- | --- |
| **Workflow** | `deploy-ovh.yml` |
| **Workflow name** | `Deploy to OVH` |
| **Trigger** | `workflow_run` — after the `Build` workflow completes on `main` |
| **Manual trigger** | Yes (`workflow_dispatch`) — optional `run_id` input; defaults to the latest successful `Build` run on `main` |
| **Runner** | `ubuntu-24.04` (GitHub-hosted) |
| **Environment** | `inventory.metanull.eu` (https://inventory.metanull.eu) |
| **Concurrency** | Group: `deploy-ovh`, cancel-in-progress: `false` |

**Job: deploy** (*Deploy to OVH VPS*)

- Only runs when triggered manually, or when the upstream `Build` run concluded `success`
- Resolves the `Build` run id and head SHA, then downloads the `release-<sha>` artifact from it
- Verifies `release.tar.gz` is present
- Sets up the SSH key, checks TCP reachability of port 22, and verifies SSH authentication (up to 3 attempts with backoff, failing fast on an auth rejection)
- Uploads `release.tar.gz` and `scripts/deploy.sh` to the VPS, then runs the deploy script
- Cleans up the uploaded files on the VPS (`if: always()`)

**Permissions**

- `contents: read` - For reading repository contents
- `actions: read` - For listing and downloading artifacts from the `Build` run

**Secrets**

| Secret | Description |
| --- | --- |
| `VPS_HOST` | Hostname or IP of the OVH VPS |
| `VPS_SSH_USER` | SSH user used for deployment |
| `VPS_SSH_KEY` | Private SSH key for that user |

---

### Deploy Documentation to GitHub Pages

Generates and deploys the Jekyll-based static documentation website to GitHub Pages. This workflow calls Python scripts to generate commit history and API client documentation.

See [/docs/README.md](../../docs/README.md) for complete Jekyll site documentation.

**Workflow properties**

| Property | Value |
| --- | --- |
| **Workflow** | `continuous-deployment_github-pages.yml` |
| **Workflow name** | `Documentation` |
| **Trigger** | Push to `main` branch |
| **Manual trigger** | Yes (`workflow_dispatch`) |
| **Runner** | `ubuntu-latest` (GitHub-hosted) |
| **Concurrency** | Group: `pages-deploy`, cancel-in-progress: `true` |

**Jobs**

1. **build** - Generates and builds documentation
   - Checks out repository with full Git history (`fetch-depth: 0`)
   - Sets up Python 3.x
   - Sets up Ruby 3.2.3 (working directory `docs`)
   - Installs Ruby dependencies with `bundle install`
   - **Generates commit history documentation** - Calls `python scripts/generate-commit-docs.py`. See [/scripts/README.md](../../scripts/README.md#generating-the-git-commit-history)
   - **Generates API client documentation** - Calls `python scripts/generate-client-docs.py`. See [/scripts/README.md](../../scripts/README.md#generating-the-api-client-npm-packages-static-documentation)
   - Builds Jekyll site with `bundle exec jekyll build`
   - Uploads `docs/_site` as the GitHub Pages artifact

2. **deploy** *(needs `build`)* - Deploys to GitHub Pages
   - Uses `actions/deploy-pages` to publish the site
   - Sets environment to `github-pages`
   - Outputs deployment URL

**Permissions**

- `contents: read` - For reading repository contents (workflow default)
- `contents: write` - Overridden on the `build` job, for committing generated documentation
- `pages: write` - For deploying to GitHub Pages
- `id-token: write` - For GitHub Pages authentication

**Scripts called**

This workflow depends on the following scripts:
- `generate-commit-docs.py` - Converts Git commit history into Jekyll markdown pages. See [/scripts/README.md](../../scripts/README.md#generating-the-git-commit-history)
- `generate-client-docs.py` - Converts TypeScript API client docs into Jekyll markdown pages. See [/scripts/README.md](../../scripts/README.md#generating-the-api-client-npm-packages-static-documentation)

For Jekyll site documentation, see [/docs/README.md](../../docs/README.md)

**Usage**

This workflow runs automatically on push to `main`. For manual deployment:

```bash
# Trigger via GitHub UI: Actions > Documentation > Run workflow
```

**Links**

| Reference | URL |
| --- | --- |
| GitHub Pages | [https://pages.github.com/](https://pages.github.com/) |
| Documentation Site | [https://metanull.github.io/inventory-app](https://metanull.github.io/inventory-app) |
| Jekyll Documentation | [https://jekyllrb.com/docs/](https://jekyllrb.com/docs/) |

---

### Publish API Client Package

Regenerates the TypeScript API client from the OpenAPI specification and publishes it to GitHub Packages when the generated client actually changed.

**Workflow properties**

| Property | Value |
| --- | --- |
| **Workflow** | `publish-api-client.yml` |
| **Workflow name** | `Publish API Client` |
| **Trigger** | Push to `main` limited to the paths `app/**`, `routes/api.php`, `config/scramble.php` |
| **Manual trigger** | Yes (`workflow_dispatch`) |
| **Runner** | `ubuntu-latest` (GitHub-hosted) |
| **Concurrency** | Group: `publish-api-client-${{ github.ref }}`, cancel-in-progress: `true` |

**Jobs**

1. **build-api-client** (*Build API Client Package*) - Regenerates the client
   - Detects whether the run is on GitHub Actions or under `act`, via the `detect-environment` composite action
   - Sets up Node.js 20 (GitHub Packages registry) and Java 17 (required by the OpenAPI generator)
   - Generates the client from `docs/_openapi/api.json` via the `generate-api-client` composite action, which also reports `has-changes`
   - Uploads the `api-client` artifact (1-day retention) only when there are changes and the run is on GitHub Actions
   - Outputs: `has-changes`, `is_github`, `is_act`

2. **publish-api-client** *(needs `build-api-client`)* (*Publish API Client to GitHub Packages*) - Publishes the package
   - Only runs when `has-changes == 'true'` and `is_github == 'true'`
   - Downloads the `api-client` artifact
   - Publishes it via the `publish-npm-package` composite action, authenticated with the `GH_PACKAGE_TOKEN` secret

**Permissions**

- `contents: read` - For reading repository contents
- `packages: write` - For publishing to GitHub Packages

**Secrets**

| Secret | Description |
| --- | --- |
| `GH_PACKAGE_TOKEN` | Token used to publish `@metanull/inventory-app-api-client` to GitHub Packages |

**Usage**

This workflow runs automatically when API-affecting paths change on `main`. For manual publishing:

```bash
# Trigger via GitHub UI: Actions > Publish API Client > Run workflow
```

Alternatively, you can generate and publish manually using the scripts:

```powershell
# See: /scripts/README.md#publishing-the-api-client-npm-package-to-the-github-packages-npm-registry
. ./scripts/publish-api-client.ps1 -Credential (Get-Credential)
```

See also [/scripts/README.md](../../scripts/README.md#generating-the-api-client-npm-package) for `generate-api-client.ps1`.

**Links**

| Reference | URL |
| --- | --- |
| GitHub Packages | [https://github.com/features/packages](https://github.com/features/packages) |
| API Client Package | [https://github.com/metanull/inventory-app/pkgs/npm/inventory-app-api-client](https://github.com/metanull/inventory-app/pkgs/npm/inventory-app-api-client) |
| Publishing Node.js Packages | [https://docs.github.com/en/actions/publishing-packages/publishing-nodejs-packages](https://docs.github.com/en/actions/publishing-packages/publishing-nodejs-packages) |

---

### Deploy Dataset Viewers to OVH

One workflow per dataset viewer. Each builds its Vite viewer against the **latest published** `@metanull/<dataset>-data` package and copies the build output to the OVH VPS over SSH.

**Workflows**

| Dataset | Workflow | Path filter | Vite base | Target directory | URL |
| --- | --- | --- | --- | --- | --- |
| `baroqueart` | `deploy-viewer-baroqueart-ovh.yml` | `scripts/viewers/baroqueart/**` | `/baroqueart/` | `/opt/baroqueart/` | https://inventory.metanull.eu/baroqueart/ |
| `islamicart` | `deploy-viewer-islamicart-ovh.yml` | `scripts/viewers/islamicart/**` | `/islamicart/` | `/opt/islamicart/` | https://inventory.metanull.eu/islamicart/ |
| `sharinghistory` | `deploy-viewer-sharinghistory-ovh.yml` | `scripts/viewers/sharinghistory/**` | `/sharinghistory/` | `/opt/sharinghistory/` | https://inventory.metanull.eu/sharinghistory/ |
| `amulets` | `deploy-viewer-amulets-ovh.yml` | `scripts/viewers/amulets/**` | `/amulets/` | `/opt/amulets/` | https://inventory.metanull.eu/amulets/ |

> **`amulets` is not deployable yet.** `@metanull/amulets-data` has not been
> published, so step 3 below carries `if: false` and the Nginx alias block does
> not exist on the VPS. Do not dispatch it. Both are lifted in the same change
> that publishes the package — see
> [`scripts/viewers/amulets/README.md`](../../scripts/viewers/amulets/README.md#what-must-change-once-the-package-is-published).

**Workflow properties** (identical apart from the dataset name)

| Property | Value |
| --- | --- |
| **Trigger** | Push to `main` limited to the viewer's own path filter |
| **Manual trigger** | Yes (`workflow_dispatch`) |
| **Runner** | `ubuntu-latest` (GitHub-hosted) |
| **Environment** | `inventory.metanull.eu` |
| **Concurrency** | Group: `deploy-viewer-<dataset>-ovh`, cancel-in-progress: `false` |

**Job: build-and-deploy** (*Build and Deploy `<dataset>` Viewer*)

1. Sets up Node.js `lts/Krypton` against GitHub Packages for the `@metanull` scope
2. Installs viewer dependencies with `npm ci`
3. Runs `npm install @metanull/<dataset>-data@latest` — the newest data package is always pulled, regardless of what `package-lock.json` pins, so the viewer reflects current data
4. Builds with `npm run build -- --base=/<dataset>/`
5. Sets up SSH, checks VPS connectivity and verifies SSH authentication
6. Copies `dist/` to the target directory on the VPS with `scp`
7. Removes the SSH key (`if: always()`)

**Permissions**

- `contents: read` - For reading repository contents
- `packages: read` - Required to pull `@metanull/<dataset>-data` from GitHub Packages

**Secrets**

| Secret | Description |
| --- | --- |
| `VPS_HOST` | Hostname or IP of the OVH VPS |
| `VPS_SSH_USER` | SSH user used for deployment |
| `VPS_SSH_KEY` | Private SSH key for that user |

---

## Automation Workflows

### Dependabot Configuration

Dependabot is configured in `.github/dependabot.yml` to keep dependencies up to date across the repository.

> **This file is maintained by hand — the one piece of CI config that is.** Dependabot config is static YAML with no scripting, so it cannot enumerate directories the way the `Exporter Validation` and `Dependency Audit` matrices do. **Adding a Node project under `scripts/` means adding an entry here too.**
>
> **This is now enforced, not merely documented.** The `dependabot-coverage` job in [CI](#ci) runs `scripts/check-dependabot-coverage.sh` on every pull request and blocks the merge when this file and the tree disagree. Documentation alone had already failed once: `scripts/viewers/amulets` shipped in PR #1566 with no entry — about an hour after PR #1557 wrote the rule down — and had to be patched in PR #1572.
>
> Generating this file from a template was considered and **rejected**: Dependabot reads it as static YAML from the default branch, so a generated copy would still have to be committed, leaving the same "did you re-run the generator?" gap it was meant to close, with a build artefact in the tree as the price. The file stays hand-written; the check keeps it honest.

**What the check asserts**

1. Every `package.json` under `scripts/**` — plus the root and `spa` — has an `npm` entry with the matching `directory:`.
2. Every `npm` entry's `directory:` resolves to a `package.json` that exists, so a deleted or renamed project is caught too.
3. Every viewer entry carries `registries: [npm-github]` and no exporter entry does.

On failure it names each offender and prints the exact YAML block to paste. Run it locally before pushing (no host-side tooling — it runs in a container):

```sh
docker run --rm -v "$PWD:/repo" -w /repo --entrypoint sh mikefarah/yq:4 \
  scripts/check-dependabot-coverage.sh
```

**Ecosystems monitored**

| Ecosystem | Directory | Schedule | Registry |
| --- | --- | --- | --- |
| `composer` | `/` | Weekly | packagist.org (public) |
| `npm` | `/` | Weekly | npm.pkg.github.com (GitHub) |
| `npm` | `/spa` | Weekly | npm.pkg.github.com (GitHub) |
| `npm` | `/scripts/importer` | Weekly | registry.npmjs.org (public) |
| `npm` | `/scripts/site-i18n` | Weekly | registry.npmjs.org (public) |
| `npm` | `/scripts/exporters/amulets` | Weekly | registry.npmjs.org (public) |
| `npm` | `/scripts/exporters/baroqueart` | Weekly | registry.npmjs.org (public) |
| `npm` | `/scripts/exporters/carpets` | Weekly | registry.npmjs.org (public) |
| `npm` | `/scripts/exporters/islamicart` | Weekly | registry.npmjs.org (public) |
| `npm` | `/scripts/exporters/sharinghistory` | Weekly | registry.npmjs.org (public) |
| `npm` | `/scripts/viewers/amulets` | Weekly | npm.pkg.github.com (GitHub) |
| `npm` | `/scripts/viewers/baroqueart` | Weekly | npm.pkg.github.com (GitHub) |
| `npm` | `/scripts/viewers/islamicart` | Weekly | npm.pkg.github.com (GitHub) |
| `npm` | `/scripts/viewers/sharinghistory` | Weekly | npm.pkg.github.com (GitHub) |
| `github-actions` | `/` | Weekly | github.com (public) |

Every exporter reads the database and writes JSON, so none consumes an `@metanull` package and none needs the authenticated registry. Every viewer installs `@metanull/<dataset>-data` from GitHub Packages and therefore needs `registries: [npm-github]`.

**GitHub Packages registry access**

The `npm` ecosystems that consume `@metanull` packages reference the GitHub Packages registry (`npm.pkg.github.com`), which requires authentication even for packages in the same organization. The registry token is configured as:

```yaml
registries:
  npm-github:
    type: npm-registry
    url: https://npm.pkg.github.com
    token: ${{secrets.DEPENDABOT_GITHUB_PACKAGES_TOKEN}}
```

Dependabot version updates run on Dependabot's own infrastructure, **not** on GitHub Actions runners. This means the automatically provided `GITHUB_TOKEN` is **not** available as a secret in `dependabot.yml`. Instead, a Personal Access Token (PAT) must be stored as a **Dependabot secret**.

> **Note**: The `${{secrets.GITHUB_TOKEN}}` approach only works when "Dependabot on Actions runners" is enabled in the repository settings (**Settings > Code security > Dependabot**). This feature is not available on all GitHub plans and is not always accessible for free public repositories.

**Setup instructions**

1. Create a Personal Access Token (classic) with `read:packages` scope
2. Store it as a **Dependabot secret** (not a regular Actions secret) named `DEPENDABOT_GITHUB_PACKAGES_TOKEN` under **Settings > Secrets and variables > Dependabot**

> Note: Dependabot secrets (under the Dependabot tab) are separate from Actions secrets (under the Actions tab). A secret in the Actions tab is **not** accessible to Dependabot.

**Links**

| Reference | URL |
| --- | --- |
| Dependabot configuration options | [https://docs.github.com/en/code-security/dependabot/dependabot-version-updates/configuration-options-for-the-dependabot.yml-file](https://docs.github.com/en/code-security/dependabot/dependabot-version-updates/configuration-options-for-the-dependabot.yml-file) |
| Dependabot on Actions runners | [https://docs.github.com/en/code-security/dependabot/working-with-dependabot/about-dependabot-on-github-actions-runners](https://docs.github.com/en/code-security/dependabot/working-with-dependabot/about-dependabot-on-github-actions-runners) |
| Configuring access to private registries | [https://docs.github.com/en/code-security/dependabot/working-with-dependabot/configuring-access-to-private-registries-for-dependabot](https://docs.github.com/en/code-security/dependabot/working-with-dependabot/configuring-access-to-private-registries-for-dependabot) |

---

### Merge Dependabot PR

Automatically approves and enables auto-merge for eligible Dependabot pull requests.

**Workflow properties**

| Property | Value |
| --- | --- |
| **Workflow** | `merge-dependabot-pr.yml` |
| **Workflow name** | `Dependabot Auto-Merge` |
| **Trigger** | `pull_request_target` (any PR activity) |
| **Manual trigger** | No |
| **Runner** | `ubuntu-latest` (GitHub-hosted) |
| **Condition** | Only runs if PR author is `dependabot[bot]` |

**Job: dependabot**

1. Fetches Dependabot PR metadata using the `dependabot/fetch-metadata` action
2. Determines auto-merge eligibility from the package ecosystem and update type
3. Approves eligible PRs using `gh pr review --approve`
4. Enables auto-merge on eligible PRs using `gh pr merge --auto --squash`

**Permissions**

- `pull-requests: write` - For approving and merging PRs
- `contents: write` - For merging changes

**Behavior**

- **`github_actions` ecosystem**: Auto-merged at any semver level, including major — Actions bumps only touch the CI pipeline, never shipped application code, and a broken one fails the same PR's required checks immediately
- **Application dependencies (npm/composer), minor and patch updates**: Automatically approved and enabled for auto-merge
- **Application dependencies (npm/composer), major updates**: Require manual review (not auto-approved)

**Usage**

This workflow runs automatically when Dependabot opens a pull request. No manual intervention is needed for eligible updates.

**Links**

| Reference | URL |
| --- | --- |
| Dependabot | [https://docs.github.com/en/code-security/dependabot](https://docs.github.com/en/code-security/dependabot) |
| dependabot/fetch-metadata | [https://github.com/dependabot/fetch-metadata](https://github.com/dependabot/fetch-metadata) |

---

## Composite Actions

Repeated setup and publishing steps live in [`.github/actions`](../actions) and are referenced from workflows with `uses: ./.github/actions/<name>`.

| Action | Purpose | Key inputs / outputs | Used by |
| --- | --- | --- | --- |
| `setup-backend` | Installs PHP (default 8.5) with the project's extensions, installs Composer dependencies, creates `.env` from `.env.local.example`, generates the app key and migrates the database | Inputs: `php-version`, `tools`, `coverage` | `continuous-integration.yml` |
| `setup-node-project` | Installs Node.js `lts/Krypton`, enables Corepack and runs `npm ci` in a given directory | Inputs: `working-directory`, `registry-url`, `node-auth-token` | `continuous-integration.yml`, `dependency-audit.yml` |
| `detect-environment` | Detects whether the run is on GitHub Actions or under `act` | Outputs: `is_github`, `is_act`, `environment` | `publish-api-client.yml` |
| `generate-api-client` | Generates the TypeScript client from the OpenAPI spec, computes the next dev version, applies the templates in `.github/templates/api-client/`, and reports whether the core files changed | Inputs: `openapi_spec_path`, `output_directory`; Output: `has-changes` | `publish-api-client.yml` |
| `publish-npm-package` | Normalizes the package version for npm, configures registry authentication, and publishes with the `dev` or `latest` tag | Inputs: `package_directory`, `registry`, `token` | `publish-api-client.yml` |

---

## Workflow Dependencies

Several workflows interact with scripts, composite actions and other workflows:

| Workflow | Depends On | Triggers |
| --- | --- | --- |
| `continuous-integration.yml` | `setup-backend`, `setup-node-project`, `scripts/check-dependabot-coverage.sh` | - |
| `dependency-audit.yml` | `setup-node-project` | - |
| `build.yml` | - | `deploy-ovh.yml` (via `workflow_run`) |
| `deploy-ovh.yml` | `build.yml` artifact, `scripts/deploy.sh` | - |
| `continuous-deployment_github-pages.yml` | [/scripts/README.md](../../scripts/README.md) scripts | - |
| `publish-api-client.yml` | `detect-environment`, `generate-api-client`, `publish-npm-package`, `.github/templates/api-client/` | - |
| `deploy-viewer-*-ovh.yml` | `@metanull/<dataset>-data` on GitHub Packages | - |
| `merge-dependabot-pr.yml` | - | - |

**Scripts used by workflows:**

- `generate-commit-docs.py` - Used by `continuous-deployment_github-pages.yml`. See [/scripts/README.md](../../scripts/README.md#generating-the-git-commit-history)
- `generate-client-docs.py` - Used by `continuous-deployment_github-pages.yml`. See [/scripts/README.md](../../scripts/README.md#generating-the-api-client-npm-packages-static-documentation)
- `deploy.sh` - Uploaded to the VPS and executed by `deploy-ovh.yml`. See [/scripts/README.md](../../scripts/README.md#deployment-scripts)
- `check-dependabot-coverage.sh` - Run by the `dependabot-coverage` job in `continuous-integration.yml`. See [/scripts/README.md](../../scripts/README.md#dependabot-coverage-check)

---

## Contributing

When adding new workflows:

1. Add description to this README
2. Document triggers, jobs, permissions, and environment variables
3. Update the workflow dependencies table
4. Add cross-references to [/scripts/README.md](../../scripts/README.md) if applicable
5. Test both manual and automated execution
6. Validate workflow syntax with `node scripts/validate-workflows.cjs`. See [/scripts/README.md](../../scripts/README.md#validation-of-the-workflow-files)
