# Inventory Management System

[![github](https://img.shields.io/badge/Source-github-151013.svg?logo=github&logoColor=white&labelColor=555555)](https://github.com/metanull/inventory-app)
[![github](https://img.shields.io/badge/Documentation-github-878787.svg?logo=github&logoColor=white&labelColor=8a8a9a)](https://metanull.github.io/inventory-app/)
[![PHP 8.2+](https://img.shields.io/badge/php-8.2+-777bb3.svg?logo=php&logoColor=white&labelColor=555555)](https://php.net)
[![Laravel 12](https://img.shields.io/badge/laravel-12-f05340.svg?logo=laravel&logoColor=ffffff&labelColor=6c6c6c)](https://laravel.com)
[![LICENSE](https://img.shields.io/badge/license-MIT-428f7e.svg?logo=open%20source%20initiative&logoColor=white&labelColor=555555)](https://github.com/metanull/inventory-app/blob/main/LICENSE)

The Inventory Management System is the digital backbone of **Museum With No Frontiers (MWNF)**. It stores and serves the inventory of museum artifacts, monuments, and related cultural heritage content managed by partner institutions around the world.

## Quick Links

- 📚 **[Full Documentation](https://metanull.github.io/inventory-app/)** — Complete guides and references
- 📖 **[Core Concepts](https://metanull.github.io/inventory-app/concepts)** — Understand what the system does (start here)
- 🔌 **[API Documentation](http://localhost:8000/docs/api)** — Interactive Swagger UI (when running locally)
- 📦 **[npm Package](https://github.com/metanull/inventory-app/packages)** — TypeScript API client

## What the System Does

- **Manages cultural heritage inventory** — items (artifacts, monuments), partners (museums, institutions), collections (exhibitions, galleries, thematic trails), and projects.
- **Supports multi-language, multi-audience content** — every item can have translations in multiple languages, each tailored to a specific audience (general public, academic, educational, etc.).
- **Handles images** — upload, process, and attach photographs to items, collections, and partners.
- **Controls access** — role-based permissions determine who can view, create, edit, or delete records.

For a detailed explanation of every entity and business rule, see the **[Core Concepts](https://metanull.github.io/inventory-app/concepts)** page.

## What's Inside This Repository

This **monorepo** contains:

| Component | Description |
| --------- | ----------- |
| **Web Interface** | The main production UI — server-rendered pages for managing all inventory data (Blade/Livewire) |
| **REST API** | Authenticated endpoints for programmatic access, with OpenAPI documentation |
| **TypeScript API Client** | Auto-generated npm package for consuming the API from external applications |
| **SPA Demo** | A Vue 3 reference app showing how to use the API client (not for production use) |
| **Documentation Site** | Jekyll-based docs deployed to GitHub Pages |

### Pipelines Status

[![Continuous Integration](https://github.com/metanull/inventory-app/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/continuous-integration.yml)
[![CodeQL](https://github.com/metanull/inventory-app/actions/workflows/github-code-scanning/codeql/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/github-code-scanning/codeql)
[![Build](https://github.com/metanull/inventory-app/actions/workflows/build.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/build.yml)
[![Deploy](https://github.com/metanull/inventory-app/actions/workflows/deploy.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/deploy.yml)
[![GitHub Pages](https://github.com/metanull/inventory-app/actions/workflows/continuous-deployment_github-pages.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/continuous-deployment_github-pages.yml)
[![Publish API Client](https://github.com/metanull/inventory-app/actions/workflows/publish-api-client.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/publish-api-client.yml)
[![Dependabot](https://github.com/metanull/inventory-app/actions/workflows/dependabot/dependabot-updates/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/dependabot/dependabot-updates)

## Getting Started

### Prerequisites

- **PHP 8.2+** with extensions: fileinfo, zip, sqlite3, pdo_sqlite, gd, exif
- **Composer** — PHP dependency management
- **Node.js 24+** — Frontend asset compilation
- **SQLite** (development) or **MariaDB** (production)

### Installation

```powershell
# Clone and navigate
git clone https://github.com/metanull/inventory-app.git
Set-Location inventory-app

# Install dependencies
composer install
npm install --no-audit --no-fund

# Environment setup
Copy-Item .env.example .env -Force
php artisan key:generate

# (Optional) Download sample images for seeding
.\scripts\download-seed-images.ps1

# Initialize database
php artisan migrate --seed

# Start development servers (Laravel + Vite + queue worker)
composer dev
```

**Access the application:**
- **Web Interface**: http://localhost:8000/web
- **API Docs**: http://localhost:8000/docs/api

### Testing

```powershell
# Backend tests
composer ci-test

# SPA Demo tests (from /spa directory)
Push-Location spa
npm test
Pop-Location
```

## Using the API Client (External Developers)

To consume the API from your own application, use the auto-generated TypeScript client:

1. **Configure npm** — create `.npmrc` in your project:

    ```ini
    @metanull:registry=https://npm.pkg.github.com
    //npm.pkg.github.com/:_authToken=YOUR_GITHUB_TOKEN
    ```

    Get a [GitHub PAT](https://github.com/settings/tokens) with `read:packages` permission.

2. **Install**

    ```bash
    npm install @metanull/inventory-app-api-client@latest
    ```

3. **Use**

    ```typescript
    import { Configuration, DefaultApi } from '@metanull/inventory-app-api-client';

    const api = new DefaultApi(new Configuration({
      basePath: 'https://your-api-url.com'
    }));

    const items = await api.itemIndex();
    console.log(items.data);
    ```

## Contributing

- ✅ All tests must pass
- ✅ All linters must pass (Laravel Pint, ESLint)
- ✅ No TypeScript errors (strict typing)
- ✅ Branch-based workflow (no direct `main` commits)

> **For complete contribution guidelines**, see the [Full Documentation](https://metanull.github.io/inventory-app/).

