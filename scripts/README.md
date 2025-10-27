# Scripts Directory

This directory contains automation scripts for the Inventory Management API project.

## Script Overview

### Jekyll Documentation (PowerShell)

#### `jekyll-build.ps1`
**Purpose:** Builds the Jekyll documentation site with automatic Ruby path detection.

**Usage:**
```powershell
# Build with defaults
. ./scripts/jekyll-build.ps1

# Build with custom base URL
. ./scripts/jekyll-build.ps1 -BaseUrl "/my-app"

# Clean build
. ./scripts/jekyll-build.ps1 -Clean
```

**Triggered by:**
- Manually by developers for local testing
- Before committing documentation changes

**Requirements:**
- WSL (Windows Subsystem for Linux) installed
- Ruby installed in WSL (user-installed preferred)
- Jekyll and bundler gems installed

**Output:**
- Static site in `docs/_site/` directory
- Ready for deployment to GitHub Pages

---

#### `jekyll-serve.ps1`
**Purpose:** Starts a local Jekyll development server with automatic Ruby path detection.

**Usage:**
```powershell
# Serve with defaults (http://127.0.0.1:4000)
. ./scripts/jekyll-serve.ps1

# Custom port
. ./scripts/jekyll-serve.ps1 -Port 8080

# Enable LiveReload
. ./scripts/jekyll-serve.ps1 -LiveReload

# Serve on all network interfaces
. ./scripts/jekyll-serve.ps1 -Host 0.0.0.0
```

**Triggered by:**
- Manually by developers for local documentation preview
- When writing or updating documentation

**Requirements:**
- WSL (Windows Subsystem for Linux) installed
- Ruby installed in WSL (user-installed preferred)
- Jekyll and bundler gems installed

**Features:**
- Automatic Ruby path detection (prefers user install over system)
- Live preview of documentation changes
- Optional LiveReload support
- Configurable host and port

---

### Documentation Generation

#### `generate-commit-docs.py`
**Purpose:** Generates Jekyll-compatible documentation pages from Git commit history.

**Usage:**
```bash
# Run from project root
python scripts/generate-commit-docs.py
```

**Triggered by:**
- GitHub Actions workflow: `.github/workflows/continuous-deployment_github-pages.yml`
- Runs automatically on every push to `main` branch
- Can be run manually for local testing

**Output:**
- Creates markdown files in `docs/_docs/` directory
- Each file represents one commit with metadata, message, and changes
- Files are automatically processed by Jekyll into static HTML pages

---

#### `generate-client-docs.py`
**Purpose:** Generates Jekyll-compatible documentation from TypeScript API client source code.

**Usage:**
```bash
# Requires TypeScript client to be generated first
# . ./scripts/generate-api-client.ps1

# Then generate documentation
python scripts/generate-client-docs.py
```

**Triggered by:**
- GitHub Actions workflow: `.github/workflows/continuous-deployment_github-pages.yml`
- Runs automatically after commit docs generation
- Should be run after API client is regenerated

**Output:**
- Creates markdown files in `docs/api-client/` directory
- Includes API documentation, models, requests, and responses
- Auto-generates index pages with navigation

---

### API Client Management (PowerShell)

#### `composer ci-openapi-doc`
The `composer ci-openapi-doc` command generates both the API documentation and API Client documentation.

1. It cleans the api-client directory and temp_openapi_generation.log log file
2. It generates the API Documentation file (api.json) with `php artisan scramble:export --path=docs/_openapi/api.json --ansi` 
3. It generates the TypeScript API-client library and its documentation with `generate-api-client.ps1`

#### `generate-api-client.ps1`
**Purpose:** Generates TypeScript-Axios API client from OpenAPI specification.

**Usage:**
```powershell
. ./scripts/generate-api-client.ps1
```

**Triggered by:**
- The composer command `composer ci-openapi-doc`
- Manually by developers after API changes
- Required before publishing API client package

**Requirements:**
- OpenAPI Generator CLI installed
- OpenAPI specification at `docs/_openapi/api.json` **(*)**

**Output:**
- TypeScript client code in `api-client/` directory
- Package ready for publishing to npm/GitHub Packages

**(*) OpenAPI api.json:**
- dedoc:Scramble generated json documentation of the API
- Generated with `php artisan scramble:export --path=docs/_openapi/api.json --ansi`

**Generating:**
```powershell
php artisan scramble:export --path=docs/_openapi/api.json --ansi
```

**Triggered by:**
- The composer command `composer ci-openapi-doc`
- Manually by developers after API changes
- Required before publishing API client package

---

#### `generate-model-documentation.ps1`
**Purpose:** Generates comprehensive documentation for all Laravel models from their definitions and database schemas.

**Usage:**
```powershell
# Generate/update model documentation
. ./scripts/generate-model-documentation.ps1

# Force regenerate all documentation
. ./scripts/generate-model-documentation.ps1 -Force
```

**Triggered by:**
- Manually by developers after model changes
- After database schema migrations
- When model relationships or attributes are updated

**Output:**
- Auto-generated markdown files in `docs/_model/` directory
- Includes database schemas, relationships, fillable fields, casts, scopes, etc.

**Note:** The generated files should not be manually edited as they will be overwritten.

---

#### `publish-api-client.ps1`
**Purpose:** Publishes TypeScript API client to GitHub Packages.

**Usage:**
```powershell
# Requires GitHub Personal Access Token
. ./scripts/publish-api-client.ps1 -Credential (Get-Credential)
```

**Triggered by:**
- Manually by developers/maintainers
- After API client has been regenerated and tested

**Requirements:**
- Valid GitHub PAT with package write permissions
- API client must be generated first

---

### Development Tools

#### `download-seed-images.ps1`
**Purpose:** Downloads seed images for database seeding with realistic image data.

**Usage:**
```powershell
. ./scripts/download-seed-images.ps1
```

**Triggered by:**
- Manually before running optimized seeding
- One-time setup for development environments

---

#### `scan-tailwind-colors.js`
**Purpose:** Scans codebase for Tailwind CSS color usage and generates reports.

**Usage:**
```bash
node scripts/scan-tailwind-colors.js
```

**Triggered by:**
- Manually for auditing color usage
- When standardizing color scheme

---

#### `test-migrations.ps1`
**Purpose:** Tests database migrations for errors and compatibility.

**Usage:**
```powershell
. ./scripts/test-migrations.ps1
```

**Triggered by:**
- Manually before committing migration changes
- Part of local testing workflow

---

### Configuration

#### `api-client-config.psd1`
**Purpose:** Configuration file for API client generation scripts.

**Usage:**
- Referenced by `generate-api-client.ps1`
- Contains package name, version, registry settings

---

#### `Setup-GithubPackages.ps1`
**Purpose:** Configures npm/yarn to authenticate with GitHub Packages.

**Usage:**
```powershell
. ./scripts/Setup-GithubPackages.ps1
```

**Triggered by:**
- One-time setup for developers
- When setting up new development environment

---

#### `validate-workflows.cjs`
**Purpose:** Validates GitHub Actions workflow YAML files for syntax and best practices.

**Usage:**
```bash
node scripts/validate-workflows.cjs
```

**Triggered by:**
- Manually before committing workflow changes
- Part of CI/CD validation

---

## Automation Triggers Summary

| Script | Manual | GitHub Actions | Artisan | Other |
|--------|--------|---------------|---------|-------|
| `jekyll-build.ps1` | ✓ | | | |
| `jekyll-serve.ps1` | ✓ | | | |
| `generate-commit-docs.py` | ✓ | ✓ (on push to main) | | |
| `generate-client-docs.py` | ✓ | ✓ (on push to main) | | |
| `generate-api-client.ps1` | ✓ | | | |
| `generate-model-documentation.ps1` | ✓ | | | |
| `publish-api-client.ps1` | ✓ | | | |
| `download-seed-images.ps1` | ✓ | | | |
| `scan-tailwind-colors.js` | ✓ | | | |
| `test-migrations.ps1` | ✓ | | | |
| `Setup-GithubPackages.ps1` | ✓ (one-time) | | | |
| `validate-workflows.cjs` | ✓ | | | |

## Notes

- **Python scripts** require Python 3.x - it is meant to be used in pipeline workflows
- **PowerShell scripts** require PowerShell 5.1 or PowerShell Core 7+ - it is meant to be used on the developer's machine
- **Node.js scripts** require Node.js 16+
- All scripts should be run from the **project root directory**
- GitHub Actions automatically set up required dependencies

## Contributing

When adding new scripts:

1. Add description to this README
2. Document usage, triggers, and requirements
3. Update the automation triggers table
4. Add error handling and user feedback
5. Test both manual and automated execution
