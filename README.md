# Inventory Management API

[![github](https://img.shields.io/badge/Source-github-151013.svg?logo=github&logoColor=white&labelColor=555555)](https://github.com/metanull/inventory-app)
[![github](https://img.shields.io/badge/Documentation-github-878787.svg?logo=github&logoColor=white&labelColor=8a8a9a)](https://metanull.github.io/inventory-app/)
[![PHP 8.2+](https://img.shields.io/badge/php-8.2+-777bb3.svg?logo=php&logoColor=white&labelColor=555555)](https://php.net)
[![Laravel 12](https://img.shields.io/badge/laravel-12-f05340.svg?logo=laravel&logoColor=ffffff&labelColor=6c6c6c)](https://laravel.com)
[![LICENSE](https://img.shields.io/badge/license-MIT-428f7e.svg?logo=open%20source%20initiative&logoColor=white&labelColor=555555)](https://github.com/metanull/inventory-app/blob/main/LICENSE)


A **Laravel 12** application for managing Museum With No Frontiers' inventory database. Built as a modern N-tier architecture with REST API, server-rendered web interface, and TypeScript tooling.

## Quick Links

- 📚 **[Full Documentation](https://metanull.github.io/inventory-app/)** - Complete guides, architecture details, and API references
- 🔌 **[API Documentation](http://localhost:8000/docs/api)** - Interactive Swagger UI (when running locally)
- 📦 **[npm Package](https://github.com/metanull/inventory-app/packages)** - TypeScript API client
- 🎯 **[SPA Demo](http://localhost:5174/cli)** - Vue 3 reference implementation (when running locally)

## What's Inside

This **monorepo** contains:

### Backend Application (Laravel 12 + PHP 8.2)
- **REST API** (`/api` routes) - Sanctum-authenticated endpoints with OpenAPI documentation
- **Web Interface** (`/web` routes) - Server-rendered Blade templates with Livewire (main production UI)
- **Database Models** - Comprehensive inventory management system
- **Image Processing** - Event-driven upload and attachment system

### SPA Demo (Vue 3 + TypeScript)
- Reference implementation at `/cli` route
- Demonstrates API client usage
- Example for external API consumers

### Documentation Site (Jekyll)
- Auto-generated docs on GitHub Pages
- API references and deployment guides

### Pipelines status

[![Continuous Integration](https://github.com/metanull/inventory-app/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/continuous-integration.yml)
[![CodeQL](https://github.com/metanull/inventory-app/actions/workflows/github-code-scanning/codeql/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/github-code-scanning/codeql)
[![Build](https://github.com/metanull/inventory-app/actions/workflows/build.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/build.yml)
[![Deploy](https://github.com/metanull/inventory-app/actions/workflows/deploy.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/deploy.yml)
[![GitHub Pages](https://github.com/metanull/inventory-app/actions/workflows/continuous-deployment_github-pages.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/continuous-deployment_github-pages.yml)
[![Publish API Client](https://github.com/metanull/inventory-app/actions/workflows/publish-api-client.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/publish-api-client.yml)
[![Dependabot](https://github.com/metanull/inventory-app/actions/workflows/dependabot/dependabot-updates/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/dependabot/dependabot-updates)

## Core Data Model

The system uses **UUID primary keys** for scalability, with three exceptions using standardized codes:
- **Country** 🌍 - ISO 3166-1 alpha-3 codes
- **Language** 🗣️ - ISO 639-1 codes  
- **User** 👤 - Laravel integer keys (auth compatibility)

### Core Business Entities

**Item** 📦 - Central inventory entity (museum artifacts/content)
- Belongs to Partner, associated with Country
- Types: Object, Monument, Detail, Picture
- Used in Collections

**Partner** 🏛️ - Museums, institutions managing inventory
- Has primary Country
- Owns multiple Items

**Project** 🎯 - Initiatives creating collections/items
- Categorized by Context
- Multi-language support
- Launch date management

**Collection** 📚 - Item groupings with translations
- Multi-language support via CollectionTranslation
- Partner relationships with contribution levels
- Default Language and Context

**Context** 📂 - Content contextualization
- Items can have different descriptions per Context

### Supporting Models

**Tag** 🏷️ - Flexible tagging system with many-to-many Item relationships

**Image System** �️ - Event-driven image processing pipeline:
- **ImageUpload** - Tracks upload processing status
- **AvailableImage** - Temporary pool for processed images
- **ItemImage** - Permanent attachment to Items with transactional operations

## Key Features

**Authentication & Security**
- Laravel Sanctum (API tokens) + Fortify (web auth)
- Spatie permissions for role-based access control
- Comprehensive input validation via Request classes

**RESTful API**
- Full CRUD operations with consistent Resource formatting
- Pagination, model scopes, custom endpoints
- OpenAPI/Swagger documentation
- Auto-generated TypeScript client

**Image Processing**
- Event-driven upload/processing workflow
- Flexible storage (local/S3)
- Multiple formats and sizes support

> **For complete data model documentation**, see [Full Documentation](https://metanull.github.io/inventory-app/).

## Getting Started - Backend Development

### Prerequisites

- **PHP 8.2+** with extensions: fileinfo, zip, sqlite3, pdo_sqlite, gd, exif
- **Composer** - PHP dependency management
- **Node.js 20+** - Frontend asset compilation
- **SQLite** (development) or **MariaDB** (production)

### Installation (Windows)

This project is organized as a **monorepo** with the following structure:

```
inventory-app/
├── app/                      # Laravel backend application
├── routes/                   # API and web routes  
├── resources/
│   ├── css/                 # Blade/Tailwind styles
│   └── views/               # Laravel Blade templates
├── database/                # Migrations, factories, seeders
├── tests/                   # Backend tests (PHPUnit)
├── public/
│   ├── build/              # Backend build output (Blade CSS)
│   └── spa-build/          # SPA build output (Vue 3)
├── spa/                     # Vue 3 SPA Demo (separate app)
│   ├── src/                # Vue application source
│   ├── package.json        # SPA-specific dependencies
│   ├── vite.config.js      # SPA build configuration
│   └── README.md           # SPA documentation
├── docs/                    # Jekyll documentation site
├── package.json            # Backend/Blade dependencies
└── composer.json           # PHP dependencies
```

**Key Points:**
- **Backend**: Laravel 12 with server-rendered Blade templates (main production UI)
- **SPA Demo**: Vue 3 + TypeScript demo application at `/cli/` route (reference implementation)
- **Separate Builds**: Backend and SPA build independently into `/public/build/` and `/public/spa-build/`
- **Independent Development**: Each application has its own `package.json`, configs, and dependency trees

#### Prerequisites

- **PHP 8.2+** - Modern PHP version with latest features
- **Composer** - PHP dependency management
- **Node.js 20+** - Frontend asset compilation
- **SQLite** (development) or **MariaDB** (production)

#### Installation (Windows)

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

# (Optional) Only if you intend to use the SPA demo
Push-Location spa
npm install --no-audit --no-fund
Pop-Location

# Start development servers
composer dev
```

**Access the application:**
- **Web Interface**: http://localhost:8000/web
- **SPA Demo**: http://localhost:5174/cli
- **API Docs**: http://localhost:8000/docs/api

### Project Structure

```
inventory-app/
├── app/                    # Laravel backend (Models, Controllers)
├── routes/                 # API (/api) and web (/web) routes
├── resources/
│   ├── css/               # Blade/Tailwind styles
│   └── views/             # Blade templates
├── database/              # Migrations, factories, seeders
├── tests/                 # Backend tests (PHPUnit)
├── spa/                   # Vue 3 SPA Demo
│   ├── src/              # Vue source
│   └── package.json      # SPA dependencies
├── docs/                  # Jekyll documentation
└── public/
    ├── build/            # Backend build output
    └── spa-build/        # SPA build output
```

### Testing

Run the test suites:

```powershell
php artisan test --parallel    # Backend tests

Push-Location spa
npm test                       # SPA Demo tests
Pop-Location    
```

## Getting Started - SPA Development

The SPA Demo (`/spa` directory) is a Vue 3 reference implementation for consuming the API.

```powershell
Push-Location spa
npm run dev        # Starts Vite dev server on http://localhost:5174
Pop-Location
```

## Using the API Client (External Developers)

To consume this API from your own application, use the auto-generated TypeScript client.

### Setup

1. **Configure npm** - Create `.npmrc` in your project:

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
    
    api.addressIndex().then(response => console.log(response.data));
    ```

**Resources:**
- [Package Registry](https://github.com/metanull/inventory-app/packages)
- [API Documentation](http://localhost:8000/docs/api) (Swagger UI)
- SPA Demo source (`/spa` directory) - working example

## Deployment

For production deployment on **Windows Server** with Apache/MariaDB:

**Stack Requirements:**
- PHP 8.2+ (with extensions: fileinfo, zip, sqlite3, pdo_sqlite, gd, exif)
- Apache 2.4+ with mod_rewrite
- MariaDB 10.5+
- Local filesystem or AWS S3 for storage

**Quick Deploy:**
1. Review configurations in `/deployment/` directory
2. Configure environment variables (production database, storage, security)
3. Build assets: `npm run build` (root)
4. Build SPA assets: `Push-Location; npm run build; Pop-Location`
5. Deploy following Apache config templates

> **For detailed deployment guide**, see [Full Documentation](https://metanull.github.io/inventory-app/).

## Contributing & Development

**Quality Standards:**
- ✅ All tests must pass (560+ tests)
- ✅ All linters must pass (Laravel Pint, ESLint)
- ✅ No TypeScript errors (strict typing)
- ✅ Branch-based workflow (no direct `main` commits)

