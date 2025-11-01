---
layout: default
title: GitHub Workflows
parent: Development
nav_order: 5
permalink: /development/workflows/
---

The `/.github/workflows` directory contains GitHub Actions workflows for continuous integration, deployment, and automation tasks.

## Table of contents

- [Table of contents](#table-of-contents)
- [Notes](#notes)
- [Local Testing with Act](#local-testing-with-act)
  - [Configuration](#configuration)
  - [Common Commands](#common-commands)
  - [VS Code Tasks](#vs-code-tasks)
- [Continuous Integration and Testing](#continuous-integration-and-testing)
  - [Continuous Integration](#continuous-integration)
  - [Build](#build)
- [Continuous Deployment](#continuous-deployment)
  - [Deploy Laravel Application](#deploy-laravel-application)
  - [Deploy Documentation to GitHub Pages](#deploy-documentation-to-github-pages)
- [Automation Workflows](#automation-workflows)
  - [Merge Dependabot PR](#merge-dependabot-pr)
- [Workflow Dependencies](#workflow-dependencies)
- [Contributing](#contributing)

## Notes

- **Workflows run on GitHub-hosted runners** (`ubuntu-latest`) or **self-hosted runners** (`[self-hosted, windows]`) depending on the task
- **Python workflows** use Python 3.x on Ubuntu runners for documentation generation
- **PowerShell workflows** use PowerShell on Windows runners for application deployment
- Most workflows use **concurrency groups** to prevent duplicate runs and conserve resources
- Workflows are triggered by push events, pull requests, releases, or manual dispatch (`workflow_dispatch`)
- Several workflows interact with scripts in [/scripts/README.md](/development/scripts)

## Local Testing with Act

[Act](https://github.com/nektos/act) runs GitHub Actions workflows locally using Docker. This allows testing workflows before pushing to GitHub.

### Configuration

**`.actrc` - Act Configuration File**

Located at project root, contains default flags for all `act` commands:

```bash
# Use Ubuntu image for Linux-based workflows
-P ubuntu-latest=catthehacker/ubuntu:act-latest

# Use host machine for Windows workflows (no container)
-P windows-latest=-self-hosted
-P self-hosted=-self-hosted

# Default secrets file
--secret-file .secrets

# Use CI environment variables (prevents local .env from polluting tests)
--env-file .env.local.example

# Container architecture
--container-architecture linux/amd64
```

**Environment Variable Handling**

Act loads environment variables from your local `.env` file by default, which can cause issues when the local environment differs from CI. The `.actrc` file specifies `--env-file .env.local.example` to use CI-appropriate values (e.g., `DB_CONNECTION=sqlite` instead of your local `mariadb`).

### Common Commands

**Run all jobs in a workflow:**

```powershell
act pull_request -W .github/workflows/continuous-integration.yml
```

**Run a specific job:**

```powershell
act pull_request -W .github/workflows/continuous-integration.yml --job backend-lint
```

**Offline mode** (skip Docker image pulls, uses cached images):

```powershell
act pull_request -W .github/workflows/continuous-integration.yml --action-offline-mode
```

**Disable image pulling** (faster subsequent runs):

```powershell
act pull_request -W .github/workflows/continuous-integration.yml --pull=false
```

**Override environment variables:**

```powershell
act pull_request -W .github/workflows/continuous-integration.yml --env DB_CONNECTION=sqlite
```

**Use custom env file:**

```powershell
act pull_request -W .github/workflows/continuous-integration.yml --env-file .env.testing
```

**List available workflows and jobs:**

```powershell
act -l
```

### VS Code Tasks

Pre-configured tasks are available via **Terminal > Run Task** or `Ctrl+Shift+P` → "Tasks: Run Task":

**Build & Quality Checks:**
- `install` - Install all dependencies (Composer + npm for both main app and SPA)
- `check` - Run all linters and audits (Pint, ESLint, Composer/npm audit)
- `build` - Full build pipeline (check + build assets)
- `pint:run` - PHP linting with Laravel Pint
- `npm-lint:run` / `npm-lint:run:spa` - JavaScript/TypeScript linting

**Testing:**
- `test` - Run all tests (build + PHPUnit + Vitest)
- `phpunit:run` - Backend tests only (parallel)
- `npm-test:run:spa` - SPA frontend tests only

**Development Servers:**
- `dev` - Start Laravel development server
- `composer-dev:run` - Same as `dev`
- `npm-dev:run:spa` - Start Vite dev server for SPA

**Local Workflow Testing (Act):**
- `act:continuous-integration` - Run full CI workflow locally
- `act:continuous-integration:backend-lint` - Run backend linting job only
- `act:continuous-integration:backend-test` - Run backend tests job only
- `act:continuous-integration:spa-test` - Run SPA tests job only
- `act:build` - Run build workflow locally
- `act:github-pages` - Run GitHub Pages deployment workflow locally

**Act Task Configuration:**

All `act:*` tasks:
- Run `build` task as dependency first
- Use `--action-offline-mode` flag (skip Docker pulls)
- Use `--env-file .env.ci` (consistent CI environment)
- Use `-W` flag to specify workflow file

Example task definition:
```json
{
  "label": "act:continuous-integration:backend-lint",
  "type": "shell",
  "command": "act",
  "args": [
    "pull_request",
    "-W", ".github/workflows/continuous-integration.yml",
    "--action-offline-mode",
    "--env-file", ".env.ci",
    "--job", "backend-lint"
  ]
}
```

## Continuous Integration and Testing

### Continuous Integration

Validates code quality, runs tests, and ensures the application builds correctly before merging changes.

**Workflow properties**

| Property           | Value                                                          |
| ------------------ | -------------------------------------------------------------- |
| **Workflow**       | `continuous-integration.yml`                                   |
| **Trigger**        | Pull requests to `main` branch (opened, synchronize, reopened) |
| **Manual trigger** | Yes (`workflow_dispatch`)                                      |
| **Runner**         | `ubuntu-latest` (GitHub-hosted)                                |
| **Concurrency**    | Group: `ci-${{ github.ref }}`, cancel-in-progress: `true`      |

**Jobs**

1. **backend-lint** - Validates PHP/Laravel backend code quality
   - Installs PHP 8.4+ with extensions (fileinfo, zip, sqlite3, pdo_sqlite, gd, exif)
   - Validates `composer.json` and checks platform requirements
   - Installs dependencies with `composer install --no-scripts`
   - Audits dependencies for security vulnerabilities with `composer audit`
   - Creates `.env` file from `.env.local.example`
   - Generates application key with `php artisan key:generate`
   - Runs database migrations with in-memory SQLite
   - Lints code with Laravel Pint

2. **backend-tests** - Runs backend test suites
   - Matrix strategy runs 7 parallel test suites:
     - Unit, Api, Web, Configuration, Console, Event, Integration
   - Uses in-memory SQLite database (`:memory:`)
   - Runs tests with coverage and parallel execution
   - Environment variables: `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`

3. **backend-rendered-frontend-validation** - Validates Blade/Tailwind frontend
   - Installs Node.js (LTS Krypton)
   - Installs npm dependencies with `npm ci`
   - Audits npm packages with `npm audit --audit-level moderate`
   - Builds backend assets with `npm run build`

4. **spa-frontend-validation** - Validates Vue 3 SPA
   - Installs Node.js (LTS Krypton)
   - Installs SPA dependencies from `spa/` directory
   - Audits dependencies with `npm audit`
   - Lints code with `npm run lint`
   - Type-checks with TypeScript
   - Builds SPA with `npm run build`
   - Runs all tests with `npm run test:all`

5. **ci-success** - Aggregates results
   - Waits for all jobs to complete
   - Fails if any job failed
   - Provides summary of all job results

**Environment Variables**

Job-level environment variables override Docker container defaults:

```yaml
backend-lint:
  env:
    DB_CONNECTION: sqlite
    DB_DATABASE: ':memory:'

backend-tests:
  env:
    DB_CONNECTION: sqlite
    DB_DATABASE: ':memory:'
```

**Permissions**

- `contents: write` - For potential version bumps
- `pull-requests: write` - For PR comments and labels
- `packages: read` - For accessing GitHub Packages (SPA dependencies)

**Local Testing**

```powershell
# Run full workflow
act pull_request -W .github/workflows/continuous-integration.yml --action-offline-mode

# Run specific job
act pull_request -W .github/workflows/continuous-integration.yml --job backend-lint --action-offline-mode

# Or use VS Code tasks: Terminal > Run Task > act:continuous-integration
```

**Links**

| Reference                        | URL                                                                                                                                                                                                      |
| -------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| GitHub Actions workflow_dispatch | [https://docs.github.com/en/actions/using-workflows/events-that-trigger-workflows#workflow_dispatch](https://docs.github.com/en/actions/using-workflows/events-that-trigger-workflows#workflow_dispatch) |
| Act (Local Testing)              | [https://github.com/nektos/act](https://github.com/nektos/act)                                                                                                                                           |

---

### Build

Builds the Laravel application package for deployment. Creates a deployment-ready artifact with all dependencies and compiled assets.

**Workflow properties**

| Property           | Value                     |
| ------------------ | ------------------------- |
| **Workflow**       | `build.yml`               |
| **Trigger**        | Push to `main` branch     |
| **Manual trigger** | Yes (`workflow_dispatch`) |
| **Runner**         | `ubuntu-latest`           |

**Jobs**

1. **build** - Creates deployment package
   - Installs PHP 8.4+ with production extensions
   - Installs Composer dependencies with `--no-dev --optimize-autoloader`
   - Installs Node.js dependencies
   - Builds frontend assets with Vite
   - Generates `VERSION` file with commit information
   - Uploads deployment artifact with 90-day retention

**Artifact Structure**

The deployment package includes:
- Production-optimized Composer dependencies (no dev packages)
- Compiled frontend assets (`public/build/`)
- Application source code
- VERSION file with build metadata

**Local Testing**

```powershell
act push -W .github/workflows/build.yml --action-offline-mode
# Or use VS Code task: act:build
```

---

## Continuous Deployment

### Deploy Laravel Application

Deploys the Laravel application to a production environment using a symlink-based deployment strategy. Downloads pre-built artifact from the build workflow.

**Workflow properties**

| Property           | Value                    |
| ------------------ | ------------------------ |
| **Workflow**       | `deploy.yml`             |
| **Trigger**        | Push to `main` branch    |
| **Manual trigger** | Yes (`workflow_dispatch`) |
| **Runner**         | `[self-hosted, windows]` |
| **Environment**    | `MWNF-SVR`               |

**Jobs**

1. **deploy** - Deploys to staging directory with symlink swap
   - Downloads pre-built deployment artifact from build workflow
   - Downloads deployment artifact
   - Creates timestamped staging directory (`staging-YYYYMMDD-HHMMSS`)
   - Puts old application into maintenance mode (`php artisan down`)
   - Creates temporary symlink to new staging directory
   - Atomically swaps symlinks to minimize downtime
   - Cleans up obsolete staging directories (keeps last 3)
   - Implements rollback capability if symlink swap fails

3. **configure** - Configures Laravel application
   - Generates production `.env` file from `.env.example` using environment variables
   - Runs database migrations (`php artisan migrate --force`)
   - Syncs permissions and roles (`php artisan permissions:sync --production`)
   - Caches configuration, routes, and views for performance

**Environment Variables** (set in GitHub environment `MWNF-SVR`)

| Variable              | Description                      | Default                                         |
| --------------------- | -------------------------------- | ----------------------------------------------- |
| `PHP_PATH`            | Path to PHP executable           | `C:\Program Files\PHP\php.exe`                  |
| `COMPOSER_PATH`       | Path to Composer executable      | `C:\ProgramData\ComposerSetup\bin\composer.bat` |
| `NODE_PATH`           | Path to Node.js executable       | `C:\Program Files\nodejs\node.exe`              |
| `NPM_PATH`            | Path to npm executable           | `C:\Program Files\nodejs\npm.ps1`               |
| `MARIADB_PATH`        | Path to MariaDB client           | `C:\Program Files\MariaDB 10.5\bin\mysql.exe`   |
| `DEPLOY_PATH`         | Deployment base directory        | `C:\Apache24\htdocs\inventory-app`              |
| `WEBSERVER_PATH`      | Symlink location for webserver   | `C:\Apache24\htdocs\inventory-app`              |
| `APP_NAME`            | Application name                 | `inventory-app`                                 |
| `APP_ENV`             | Environment (production/staging) | `production`                                    |
| `APP_DEBUG`           | Enable debug mode                | `false`                                         |
| `APP_URL`             | Application URL                  | `http://localhost`                              |
| `DB_CONNECTION`       | Database driver                  | `mysql`                                         |
| `DB_HOST`             | Database host                    | `127.0.0.1`                                     |
| `DB_PORT`             | Database port                    | `3306`                                          |
| `API_DOCS_ENABLED`    | Enable API documentation         | `false`                                         |
| `APACHE_SERVICE_USER` | Apache service user              | `SYSTEM`                                        |
| `TRUSTED_PROXIES`     | Comma-separated proxy IPs/CIDR   | (empty)                                         |

**Environment Secrets** (set in GitHub environment `MWNF-SVR`)

| Secret             | Description                                                               |
| ------------------ | ------------------------------------------------------------------------- |
| `APP_KEY`          | Laravel application key (generate with `php artisan key:generate --show`) |
| `MARIADB_DATABASE` | Database name                                                             |
| `MARIADB_USER`     | Database username                                                         |
| `MARIADB_SECRET`   | Database password                                                         |

**Deployment Strategy**

This workflow uses a **symlink-based zero-downtime deployment**:

1. Build artifact is downloaded to a timestamped staging directory
2. Application is put into maintenance mode
3. A temporary symlink is created pointing to the new staging directory
4. The webserver symlink is atomically swapped to the new deployment
5. Old symlinks are removed
6. Old staging directories are cleaned up (keeps last 3 for rollback)

**Permissions**

- `contents: read` - For reading repository contents
- `packages: read` - For accessing GitHub Packages

**Usage**

This workflow runs automatically when changes are pushed to `main`.

**Links**

| Reference           | URL                                                                                                                                                                                                                                  |
| ------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Laravel Deployment  | [https://laravel.com/docs/12.x/deployment](https://laravel.com/docs/12.x/deployment)                                                                                                                                                 |
| GitHub Environments | [https://docs.github.com/en/actions/deployment/targeting-different-environments/using-environments-for-deployment](https://docs.github.com/en/actions/deployment/targeting-different-environments/using-environments-for-deployment) |

---

### Deploy Documentation to GitHub Pages

Generates and deploys the Jekyll-based static documentation website to GitHub Pages. This workflow calls Python scripts to generate commit history and API client documentation.

See [/docs/README.md](/development/documentation-site) for complete Jekyll site documentation.

**Workflow properties**

| Property           | Value                                             |
| ------------------ | ------------------------------------------------- |
| **Workflow**       | `continuous-deployment_github-pages.yml`          |
| **Trigger**        | Push to `main` branch                             |
| **Manual trigger** | Yes (`workflow_dispatch`)                         |
| **Runner**         | `ubuntu-latest` (GitHub-hosted)                   |
| **Concurrency**    | Group: `pages-deploy`, cancel-in-progress: `true` |

**Jobs**

1. **build** - Generates and builds documentation
   - Checks out repository with full Git history (`fetch-depth: 0`)
   - Sets up Python 3.x
   - Sets up Ruby 3.2.3
   - Installs Ruby dependencies with `bundle install`
   - **Generates commit history documentation** - Calls `python scripts/generate-commit-docs.py`. See [/scripts/README.md](/development/scripts#generating-the-git-commit-history)
   - **Generates API client documentation** - Calls `python scripts/generate-client-docs.py`. See [/scripts/README.md](/development/scripts#generating-the-api-client-npm-packages-static-documentation)
   - Builds Jekyll site with `bundle exec jekyll build`
   - Uploads artifact for GitHub Pages

2. **deploy** - Deploys to GitHub Pages
   - Uses `actions/deploy-pages@v4` to publish the site
   - Sets environment to `github-pages`
   - Outputs deployment URL

**Permissions**

- `contents: read` - For reading repository contents
- `contents: write` - For committing generated documentation (build job)
- `pages: write` - For deploying to GitHub Pages
- `id-token: write` - For GitHub Pages authentication

**Scripts called**

This workflow depends on the following scripts:

- `generate-commit-docs.py` - Converts Git commit history into Jekyll markdown pages. See [/scripts/README.md](/development/scripts#generating-the-git-commit-history)
- `generate-client-docs.py` - Converts TypeScript API client docs into Jekyll markdown pages. See [/scripts/README.md](/development/scripts#generating-the-api-client-npm-packages-static-documentation)

For Jekyll site documentation, see [/docs/README.md](/development/documentation-site)

**Usage**

This workflow runs automatically on push to `main`. For manual deployment:

```bash
# Trigger via GitHub UI: Actions > Continuous Deployment to GitHub Pages > Run workflow
```

**Links**

| Reference            | URL                                                                                  |
| -------------------- | ------------------------------------------------------------------------------------ |
| GitHub Pages         | [https://pages.github.com/](https://pages.github.com/)                               |
| Documentation Site   | [https://metanull.github.io/inventory-app](https://metanull.github.io/inventory-app) |
| Jekyll Documentation | [https://jekyllrb.com/docs/](https://jekyllrb.com/docs/)                             |

---

## Automation Workflows

### Merge Dependabot PR

Automatically approves and enables auto-merge for Dependabot pull requests that are not major version updates.

**Workflow properties**

| Property           | Value                                       |
| ------------------ | ------------------------------------------- |
| **Workflow**       | `merge-dependabot-pr.yml`                   |
| **Trigger**        | `pull_request_target` (any PR opened)       |
| **Manual trigger** | No                                          |
| **Runner**         | `ubuntu-latest` (GitHub-hosted)             |
| **Condition**      | Only runs if PR author is `dependabot[bot]` |

**Job: dependabot**

1. Fetches Dependabot PR metadata using `dependabot/fetch-metadata` action
2. Checks if update type is not a major version update
3. Approves the PR using `gh pr review --approve`
4. Enables auto-merge on the PR

**Permissions**

- `pull-requests: write` - For approving and merging PRs
- `contents: write` - For merging changes

**Behavior**

- **Minor and patch updates**: Automatically approved and enabled for auto-merge
- **Major updates**: Require manual review (not auto-approved)

**Usage**

This workflow runs automatically when Dependabot opens a pull request. No manual intervention needed for minor/patch updates.

**Links**

| Reference                 | URL                                                                                                        |
| ------------------------- | ---------------------------------------------------------------------------------------------------------- |
| Dependabot                | [https://docs.github.com/en/code-security/dependabot](https://docs.github.com/en/code-security/dependabot) |
| dependabot/fetch-metadata | [https://github.com/dependabot/fetch-metadata](https://github.com/dependabot/fetch-metadata)               |

---

## Workflow Dependencies

Several workflows interact with scripts and other workflows:

| Workflow                                 | Depends On                                         | Triggers      |
| ---------------------------------------- | -------------------------------------------------- | ------------- |
| `continuous-integration.yml`             | -                                                  | -             |
| `build.yml`                              | -                                                  | `deploy.yml`  |
| `deploy.yml`                             | `build.yml` (downloads artifact)                   | -             |
| `continuous-deployment_github-pages.yml` | [/scripts/README.md](/development/scripts) scripts | -             |
| `merge-dependabot-pr.yml`                | -                                                  | -             |

**Scripts used by workflows:**

- `generate-commit-docs.py` - Used by `continuous-deployment_github-pages.yml`. See [/scripts/README.md](/development/scripts#generating-the-git-commit-history)
- `generate-client-docs.py` - Used by `continuous-deployment_github-pages.yml`. See [/scripts/README.md](/development/scripts#generating-the-api-client-npm-packages-static-documentation)

---

## Contributing

When adding new workflows:

1. Add description to this README
2. Document triggers, jobs, permissions, and environment variables
3. Update the workflow dependencies table
4. Add cross-references to [/scripts/README.md](/development/scripts) if applicable
5. Test both manual and automated execution
6. Validate workflow syntax with `node scripts/validate-workflows.cjs`. See [/scripts/README.md](/development/scripts#validation-of-the-workflow-files)
