# Scripts

The /scripts directory contains automation and helper scripts used for documentation, management of the API client npm package, etc.

## Table of contents

- [Scripts](#scripts)
  - [Table of contents](#table-of-contents)
  - [Notes](#notes)
  - [Scripts used in CI/CD Workflows](#scripts-used-in-cicd-workflows)
    - [Auto-generation of the static documentation website](#auto-generation-of-the-static-documentation-website)
      - [Generating the Git Commit History](#generating-the-git-commit-history)
      - [Generating the API client npm package's static documentation](#generating-the-api-client-npm-packages-static-documentation)
  - [Scripts used locally during development](#scripts-used-locally-during-development)
    - [Auto-generation of the API client npm package](#auto-generation-of-the-api-client-npm-package)
      - [Authenticating with GitHub Packages](#authenticating-with-github-packages)
      - [Generating the API client npm package](#generating-the-api-client-npm-package)
      - [Publishing the API client npm package to the GitHub Packages npm registry](#publishing-the-api-client-npm-package-to-the-github-packages-npm-registry)
    - [Generation of the static documentation website](#generation-of-the-static-documentation-website)
      - [Generating the static documentation website locally](#generating-the-static-documentation-website-locally)
      - [Running the static documentation website locally](#running-the-static-documentation-website-locally)
      - [Auto-generating the Model documentation](#auto-generating-the-model-documentation)
    - [Local CI/CD Build Simulation](#local-cicd-build-simulation)
    - [Development helpers](#development-helpers)
      - [Image seeding](#image-seeding)
      - [Migration testing](#migration-testing)
      - [Validation of the Workflow files](#validation-of-the-workflow-files)
      - [Running tests in parallel](#running-tests-in-parallel)

## Notes

- **Python scripts** require Python 3.x - it is meant to be used in pipeline workflows
- **PowerShell scripts** require PowerShell 5.1 or PowerShell Core 7+ - it is meant to be used on the developer's machine
- **Node.js scripts** require Node.js 16+
- All scripts should be run from the **project root directory**
- GitHub Actions automatically set up required dependencies

## Scripts used in CI/CD Workflows

### Auto-generation of the static documentation website

These scripts are triggered by the CI/CD Workflow action `.github/workflows/continuous-deployment_github-pages.yml` responsible for deploying the static documentation website to [github.io](https://metanull.github.io). 

See:
- [/.github/workflows/README.md](../.github/workflows/README.md#deploy-documentation-to-github-pages) for workflow details
- [/docs/README.md](../docs/README.md) for Jekyll site documentation

#### Generating the Git Commit History

Converts Git commit history into Jekyll-compatible markdown pages.

These files are integrated by Jekyll into the static documentation website under `/inventory-app/development/archive`.

The script is called by CI workflows on push to main. 

See:
- [/.github/workflows/README.md](../.github/workflows/README.md#deploy-documentation-to-github-pages) for workflow details
- [/docs/README.md](../docs/README.md#script-generate-commit-documentation) for Jekyll integration

**Script properties**

| Property | Value |
| --- | --- |
| **Script** | `generate-commit-docs.py` |
| **Invoker** | Invoked by `.github/workflows/continuous-deployment_github-pages.yml` on **push** to **main**. See [/.github/workflows/README.md](../.github/workflows/README.md#deploy-documentation-to-github-pages) |
| **Input** | It reads from git directly |
| **Output** | `/docs/_docs/**/*.md` |
| **Log** | `/docs/commit-docs.log` |

**Links**

| Reference | Url |
| --- | --- |
| Git Commit History | [https://metanull.github.io/inventory-app/development/archive](https://metanull.github.io/inventory-app/development/archive) |

**Usage:**
```bash
# Run from project root
python scripts/generate-commit-docs.py
```

#### Generating the API client npm package's static documentation

Transforms the TypeScript API Client markdown files (auto-generated) into Jekyll-compatible markdown pages; fixes the relative hyperlinks they contain; and generates an Index markdown file.

These files are integrated by Jekyll into the static documentation website under `/inventory-app/api-client/`.

The script is called by CI workflows on push to main. 

See:
- [/.github/workflows/README.md](../.github/workflows/README.md#deploy-documentation-to-github-pages) for workflow details
- [/docs/README.md](../docs/README.md#script-generate-api-client-documentation) for Jekyll integration

**Script properties**

| Property | Value |
| --- | --- |
| Script | `generate-client-docs.py` |
| Invoker | Invoked by `.github/workflows/continuous-deployment_github-pages.yml` on **push** to **main**. See [/.github/workflows/README.md](../.github/workflows/README.md#deploy-documentation-to-github-pages) |
| Input | `/api-client/docs/*.md` - These files are auto-generated during development and not directly suitable for integration by Jekyll. See [Generating the API client npm package](#generating-the-api-client-npm-package) |
| Output | `/docs/api-client/*.md` |
| Log | `/docs/client-docs.log` |

**Links**

| Reference | Url |
| --- | --- |
| Static documentation of the API client npm package | [https://metanull.github.io/inventory-app/api-client/](https://metanull.github.io/inventory-app/api-client/) |

**Usage:**
```bash
# Requires TypeScript client to be generated first
# See: (Generating the API client npm package)

# Then generate documentation
python scripts/generate-client-docs.py
```

## Scripts used locally during development

### Auto-generation of the API client npm package

#### Authenticating with GitHub Packages

Configures authentication with GitHub Packages by creating or updating the `.npmrc` file. It is required to install the API client npm package from the GitHub Packages npm repository.

**Script properties**

| Property | Value |
| --- | --- |
| Script | `Setup-GithubPackages.ps1` |
| Invoker | Invoked by the developer **once**, or after changing their **Personal Access Token** |
| Input | The script prompts information from the user |
| Output | `/.npmrc` |
| Log | **N/A** - The script writes to the terminal |

**Usage**

```powershell
. ./scripts/Setup-GithubPackages.ps1
```

#### Generating the API client npm package

Reads the specifications of the API exposed by the Laravel project, and generates:
- an OpenApi documentation - `/docs/_openapi/api.json`
- an API client npm package - `/api-client/*`.
- static documentation of the npm package - `/api-client/docs/*.md`

**Script properties**

| Property | Value |
| --- | --- |
| Script | `generate-api-client.ps1` |
| Invoker | Invoked by the developer after **change** to the **api** |
| Input 1 | `/app` - The source code of the Laravel application |
| Input 2 | `/scripts/api-client-config.psd1` - Configuration of API client generation scripts |
| Output 1 | `/docs/_openapi/api.json` |
| Output 2 | `/api-client/package.json`, `/api-client/*.ts` |
| Output 3 | `/api-client/docs/*.md` |
| Log | **N/A** - The script writes to the terminal |

**Links**

| Reference | Url |
| --- | --- |
| Documentation | [https://metanull.github.io/inventory-app/api/](https://metanull.github.io/inventory-app/api/) |
| API's OpenAPI specification (*api.json*) | [https://metanull.github.io/inventory-app/api.json](https://metanull.github.io/inventory-app/api.json) |
| Swagger UI for the API's OpenAPI specification | [https://metanull.github.io/inventory-app/swagger-ui.html](https://metanull.github.io/inventory-app/swagger-ui.html) |
| API client npm package | [https://github.com/metanull?tab=packages&repo_name=inventory-app](https://github.com/metanull?tab=packages&repo_name=inventory-app) |
| Static documentation of the API client npm package | [https://metanull.github.io/inventory-app/api-client/](https://metanull.github.io/inventory-app/api-client/) |

**Usage**

**IMPORTANT**: **DO** use the composer command `composer ci-openapi-doc`, as it **first** generates up to date *api.json* **then** calls *generate-api-client.ps1*

```powershell
composer ci-openapi-doc
```

Alternatively, use:

```powershell
# Update the OpenAPI specification (/docs/_openapi/api.json)
# php artisan scramble:export --path=docs/_openapi/api.json --ansi

# Generate the API client npm package
#. ./scripts/generate-api-client.ps1
```

#### Publishing the API client npm package to the GitHub Packages npm registry

Publishes the API client npm package to the [GitHub Packages](https://docs.github.com/en/packages) npm registry

**Script properties**

| Property | Value |
| --- | --- |
| Script | `publish-api-client.ps1` |
| Invoker | Invoked by the developer after [Generating the API client npm package](#generating-the-api-client-npm-package) |
| Input 1 |  `/api-client/package.json`, `/api-client/*.ts`. See [Generating the API client npm package](#generating-the-api-client-npm-package) |
| Input 2 | User's GitHub personal access token, with adequate permissions |
| Output | [https://github.com/metanull/inventory-app/pkgs/npm/inventory-app-api-client](https://github.com/metanull/inventory-app/pkgs/npm/inventory-app-api-client) |
| Log | **N/A** - The script writes to the terminal |

**Links**

| Reference | Url |
| --- | --- |
| GitHub Packages | [https://docs.github.com/en/packages](https://docs.github.com/en/packages) |
| *@metanull/inventory-app-api-client* | [https://github.com/metanull/inventory-app/pkgs/npm/inventory-app-api-client](https://github.com/metanull/inventory-app/pkgs/npm/inventory-app-api-client) |
| GitHub Packages in inventory-app | [https://github.com/metanull?tab=packages&repo_name=inventory-app](https://github.com/metanull?tab=packages&repo_name=inventory-app) |

**Usage**

```powershell
# Requires API client npm package to be generated
# Requires GitHub Personal Access Token
. ./scripts/publish-api-client.ps1 -Credential (Get-Credential)
```

### Generation of the static documentation website

#### Generating the static documentation website locally

Invokes the Jekyll Ruby Gem in a Windows Subsystem for Linux (WSL) terminal; it transforms the content of /docs into a static website.

See [/docs/README.md](../docs/README.md#building-the-site) for Jekyll site documentation.

**Script properties**

| Property | Value |
| --- | --- |
| **Script** | `jekyll-build.ps1` |
| **Invoker** | Invoked by the developer after **change** to `/docs/**/*.md` |
| **Input** | `/docs` |
| **Output** | `/docs/_site/**` |
| Log | **N/A** - The script writes to the terminal |

**Usage**

```powershell
# Build with defaults
. ./scripts/jekyll-build.ps1

# Build with custom base URL
. ./scripts/jekyll-build.ps1 -BaseUrl "/my-app"

# Clean build
. ./scripts/jekyll-build.ps1 -Clean
```

**Requirements**
- WSL (Windows Subsystem for Linux) installed
- Ruby installed in WSL (user-installed required)
- Jekyll and bundler gems installed

#### Running the static documentation website locally

Serves the static documentation website on a local URL [http://localhost:4000](http://localhost:4000).

See [/docs/README.md](../docs/README.md#building-the-site) for Jekyll site documentation.

**Script properties**

| Property | Value |
| --- | --- |
| **Script** | `jekyll-serve.ps1` |
| **Invoker** | Invoked by the developer after **change** to `/docs/**/*.md` |
| **Input** | `/docs` |
| **Output 1** | `/docs/_site/**` |
| **Output 2** | [http://localhost:4000](http://localhost:4000) |
| Log | **N/A** - The script writes to the terminal |

**Links**

| Reference | Url |
| --- | --- |
| Local static documentation website | [http://localhost:4000](http://localhost:4000) |

**Usage**

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

**Requirements**
- WSL (Windows Subsystem for Linux) installed
- Ruby installed in WSL (user-installed required)
- Jekyll and bundler gems installed

#### Auto-generating the Model documentation

Auto-generates markdown documentation for Laravel models (schemas, relations, fields).

**Script properties**

| Property | Value |
| --- | --- |
| **Script** | `generate-model-documentation.ps1` |
| **Invoker** | Invoked by the developer after **change** to the **models** |
| **Input** | `/app` |
| **Output** | `/docs/_model/**` |
| Log | **N/A** - The script writes to the terminal |

**Usage**

```powershell
# Generate/update model documentation
. ./scripts/generate-model-documentation.ps1

# Force regenerate all documentation
. ./scripts/generate-model-documentation.ps1 -Force
```

### Local CI/CD Build Simulation

Simulates the GitHub continuous deployment build pipeline locally, allowing validation that changes will build successfully before pushing to GitHub.

**Script properties**

| Property | Value |
| --- | --- |
| **Script** | `Invoke-LocalCDBuild.ps1` |
| **Invoker** | Invoked by the developer after **code changes** to validate the build |
| **Input** | Git repository, branch name, `.npmrc` file (all auto-detected) |
| **Output** | **N/A** - Temp directory is cleaned up automatically |
| **Log** | **N/A** - The script writes to the terminal |

**Usage**

All parameters are auto-detected from your current working directory if not provided:

```powershell
# Run with all parameters auto-detected from current git repo
./scripts/Invoke-LocalCDBuild.ps1

# Or specify parameters explicitly
./scripts/Invoke-LocalCDBuild.ps1 `
  -RepositoryUrl "https://github.com/metanull/inventory-app.git" `
  -BranchName "main" `
  -NpmrcPath "$HOME\.npmrc"
```

**Parameters**

- **RepositoryUrl** (optional): Git repository URL. Auto-detected from `git remote origin` if not provided.
- **BranchName** (optional): Branch name to checkout. Auto-detected from current branch if not provided.
- **NpmrcPath** (optional): Path to `.npmrc` file. Defaults to `$HOME\.npmrc` if not provided.

**What it does**
1. Checks for uncommitted or staged changes (fails if any exist)
2. Verifies `.npmrc` file exists
3. Clones repository to temp directory
4. Checks out the specified branch
5. Installs PHP and NPM dependencies (production flags)
6. Builds backend assets
7. Builds SPA assets
8. Cleans up temporary files

**Exit codes**
- **0**: Success
- **1**: Failure (see error message)

### Development helpers

#### Image seeding

Downloads and stores a set of images from the internet to avoid repeating this time consuming when seeding the database.

**Script properties**

| Property | Value |
| --- | --- |
| Script | `download-seed-images.ps1` |
| Invoker | Invoked by the developer **once** |
| Input | [https://picsum.photos](https://picsum.photos) |
| Output | `/database/seeders/data/images` |
| Log | **N/A** - The script writes to the terminal |

**Links**

| Reference | Url |
| --- | --- |
| Lorem Picsum, The Lorem Ipsum for photos. | [https://picsum.photos](https://picsum.photos) |

**Usage**

```powershell
. ./scripts/download-seed-images.ps1
```

#### Migration testing

Runs database migration back and forth a small number of times to help detect issues.
It uses an array of environment files to run the tests in multiple environments.

**Script properties**

| Property | Value |
| --- | --- |
| Script | `test-migrations.ps1` |
| Invoker | Invoked by the developer after a **change** to the **migrations** |
| Input | `/database/migrations` |
| Output | **N/A** - The script writes to the terminal |
| Log | **N/A** - The script writes to the terminal |

**Usage**

```powershell
. ./scripts/test-migrations.ps1
```

#### Validation of the Workflow files

Validates all YAML workflow files.

**Script properties**

| Property | Value |
| --- | --- |
| Script | `validate-workflows.cjs` |
| Invoker | Invoked by the developer after a **change** to workflow's **`*.yml`** files using `node ./scripts/validate-workflows.cjs`. See [/.github/workflows/README.md](../.github/workflows/README.md) |
| Input | `/.github/workflows` |
| Output | **N/A** - The script writes to the terminal |
| Log | **N/A** - The script writes to the terminal |

**Links**

| Reference | Url |
| --- | --- |
| npx, Run a command from a local or remote npm package | [https://docs.npmjs.com/cli/v9/commands/npx?v=true](https://docs.npmjs.com/cli/v9/commands/npx?v=true) |

**Usage**

```powershell
node scripts/validate-workflows.cjs
```

#### Running tests in parallel

Run all tests suites in parallel.

**Script properties**

| Property | Value |
| --- | --- |
| Script | `Start-Tests.ps1` |
| Invoker | Invoked by the developer to efficiently run all tests after a **change** to the **code** |
| Input | /tests, /resource/js/**/__tests__ */ |
| Output | **N/A** - The script writes to the terminal |
| Log | **N/A** - The script writes to the terminal |

**Usage**

```powershell
.\Scripts\Start-Tests.ps1
```