# Inventory Management API

[![PHP 8.2+](https://img.shields.io/badge/php-8.2+-777bb3.svg?logo=php&logoColor=white&labelColor=555555)](https://php.net)
[![Laravel 12](https://img.shields.io/badge/laravel-12-f05340.svg?logo=laravel&logoColor=ffffff&labelColor=6c6c6c)](https://laravel.com)
[![github](https://img.shields.io/badge/Source-github-151013.svg?logo=github&logoColor=white&labelColor=555555)](https://github.com/metanull/inventory-app)
[![LICENSE](https://img.shields.io/badge/license-MIT-428f7e.svg?logo=open%20source%20initiative&logoColor=white&labelColor=555555)](https://github.com/metanull/inventory-app/blob/main/LICENSE)
[![Continuous Integration](https://github.com/metanull/inventory-app/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/continuous-integration.yml)
[![Build](https://github.com/metanull/inventory-app/actions/workflows/build.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/build.yml)
[![Deploy](https://github.com/metanull/inventory-app/actions/workflows/deploy.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/deploy.yml)
[![GitHub Pages](https://github.com/metanull/inventory-app/actions/workflows/continuous-deployment_github-pages.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/continuous-deployment_github-pages.yml)
[![Publish API Client](https://github.com/metanull/inventory-app/actions/workflows/publish-api-client.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/publish-api-client.yml)
[![CodeQL](https://github.com/metanull/inventory-app/actions/workflows/github-code-scanning/codeql/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/github-code-scanning/codeql)
[![Dependabot](https://github.com/metanull/inventory-app/actions/workflows/dependabot/dependabot-updates/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/dependabot/dependabot-updates)


---

**🌍 Check out our documentation on github page** [https://metanull.github.io/inventory-app/](https://metanull.github.io/inventory-app/)

---

The **Inventory Management API** (inventory-app) is a RESTful API designed to manage the content of the Museum With No Frontiers' inventory database. This application serves as the management layer in a modern N-tier architecture, replacing legacy systems with a scalable, maintainable, and secure solution.

## Table of Contents

- [Inventory Management API](#inventory-management-api)
  - [Table of Contents](#table-of-contents)
  - [Project Overview](#project-overview)
  - [N-Tier Architecture](#n-tier-architecture)
  - [Data Model and Features](#data-model-and-features)
    - [Core Data Model](#core-data-model)
      - [Primary Models](#primary-models)
      - [Core Business Models](#core-business-models)
      - [Supporting Models](#supporting-models)
    - [Key Features](#key-features)
      - [🔐 Authentication \& Security](#-authentication--security)
      - [🌐 RESTful API Architecture](#-restful-api-architecture)
  - [Image Processing Pipeline](#image-processing-pipeline)
    - [Image Upload and Processing Workflow](#image-upload-and-processing-workflow)
    - [Image Storage Configuration](#image-storage-configuration)
    - [Image Features](#image-features)
    - [Image Features (COMING SOON)](#image-features-coming-soon)
  - [🧪 Testing](#-testing)
  - [🔍 API Documentation](#-api-documentation)
  - [Developer's Quick Start](#developers-quick-start)
    - [Project Structure](#project-structure)
    - [Prerequisites](#prerequisites)
    - [Installation (Windows)](#installation-windows)
    - [📚 Comprehensive Documentation](#-comprehensive-documentation)
  - [Frontend developer's Quick Start](#frontend-developers-quick-start)
    - [Installation](#installation)
    - [Usage](#usage)
    - [Package Information](#package-information)
  - [Deployment](#deployment)
    - [Production Environment](#production-environment)
    - [Environment Configuration](#environment-configuration)
    - [Web Server Configuration](#web-server-configuration)
    - [CI/CD Pipeline](#cicd-pipeline)

## Project Overview

This API is part of a broader modernization effort for Museum With No Frontiers. The new architecture consists of:

- **Management REST API** (this application): Provides secure endpoints for managing and updating the inventory database.
- **Public Consultation REST API**: Grants controlled, read-only access to inventory data for public-facing applications.
- **Client-side Web Applications**: Deployed separately, these applications interact with the consultation API to present data to end users.

## N-Tier Architecture

Adopting an N-tier architecture brings several advantages:

- **Separation of Concerns**: Each layer (management API, consultation API, frontend clients) has a distinct responsibility, making the system easier to maintain and evolve.
- **Scalability**: Components can be scaled independently based on demand, improving performance and resource utilization.
- **Security**: Sensitive management operations are isolated from public access, reducing the attack surface.
- **Flexibility**: Decoupling backend and frontend allows for independent development, testing, and deployment of each component, enabling faster iteration and easier integration of new technologies.

## Data Model and Features

### Core Data Model

The Inventory Management API is built around a sophisticated data model designed to handle complex museum inventory relationships. The system uses **UUID primary keys** for scalability and distributed system compatibility, with three notable exceptions that use standardized codes:

#### Primary Models

- **Country** 🌍 - Uses ISO 3166-1 alpha-3 codes (3-letter country codes)
- **Language** 🗣️ - Uses ISO 639-1 codes (3-letter language codes)
- **User** 👤 - Uses Laravel's default integer primary keys for authentication compatibility

All other models use UUID primary keys for optimal scalability and system integration.

#### Core Business Models

**Item** 📦 - The central inventory entity representing museum artifacts or content

- Belongs to a Partner (institution or organization)
- Associated with a Country (origin or location)
- Tracks type, internal naming, and legacy system compatibility
- Used in Collections.
- Have different types (Object, Monument, Detail, Picture)

**Partner** 🏛️ - Museums, institutions, or individuals managing inventory items

- Has a primary Country for institutional location
- Can own multiple Items
- Supports organizational hierarchy and partnerships

**Project** 🎯 - Projects resulting in the creation of collection(s) or the addition of items

- Has a primary Context for categorization
- Supports multiple Languages for internationalization
- Includes launch dates and enable/disable functionality

**Collection** 📚 - Organizational groupings for items with translation support

- Contains multiple Items through collection_id foreign key
- Supports multi-language translations via CollectionTranslation model
- Partner relationships with contribution levels (Partner, Associated Partner, Minor Contributor)
- Has default Language and Context for display purposes

**Context** 📂 - Allows for contextualization of the data

- Provides contextual content organization (same Item hhas different descriptions depending on Context)
- Supports default context selection

#### Supporting Models

**Tag** 🏷️ - Content tagging and categorization system

- Flexible tagging system for items
- Supports hierarchical and multi-dimensional categorization
- Many-to-many relationships with Items via standard Laravel pivot table

**ImageUpload** 📤 - Upload tracking and processing status

- Monitors image processing workflows
- Tracks upload success/failure states
- Creates AvailableImage records upon successful processing
- Support for multiple image formats and sizes and centralizes post-processing

**AvailableImage** 🎨 - Processed image pool for attachment

- Temporary storage for processed images awaiting attachment
- Automatic cleanup when attached to models as Pictures
- Download and preview capabilities before attachment
- Support for multiple image formats and sizes

**ItemImage** 🖼️ - Image attachment system

- Images are moved out of the temporary storage and attached to Items
- Download and preview capabilities before attachment
- Transactional attachment from AvailableImage pool
- Direct download and inline viewing capabilities


### Key Features

#### 🔐 Authentication & Security

- **Laravel Sanctum** - Token-based API authentication
- **Laravel Fortify** - Web authentication
- **Laravel Spatie** - Granualt permission management by Role-base access control.
- **Input Validation** - Comprehensive validation for all endpoints using Request classes

#### 🌐 RESTful API Architecture

- **Complete CRUD Operations** - Full Create, Read, Update, Delete functionality
- **Resource Controllers** - Consistent API response formatting
- **HTTP Status Codes** - Proper REST status code implementation
- **Pagination Support** - Efficient handling of large datasets
- **Model Scopes** - Predefined query filters for common operations
    - `Project::visible()` - Get all visible projects (enabled, launched, and launch_date passed)
    - `Language::default()` - Get default language
    - `Context::default()` - Get default context
- **Custom Endpoints** - Beyond standard REST operations, the API provides specialized endpoints. E.g. To set the default language/context or to convert markdown to html and vice-cersa.

## Image Processing Pipeline

The application features a image processing and attachment system:

### Image Upload and Processing Workflow

1. **Upload**: Images are uploaded via `POST /api/image-upload` and processed asynchronously
2. **Processing**: Background events resize, validate, and optimize images
3. **Available Pool**: Successfully processed images become `AvailableImage` records
4. **Attachment**: Images are attached to models via transactional operations

### Image Storage Configuration

The application uses a flexible storage configuration system with clear separation:

```powershell
# Upload temporary storage
UPLOAD_IMAGES_DISK=local_upload_images
UPLOAD_IMAGES_PATH=uploads/images

# Processed images awaiting attachment
AVAILABLE_IMAGES_DISK=local_available_images
AVAILABLE_IMAGES_PATH=available/images

# Permanently attached pictures
PICTURES_DISK=local_pictures
PICTURES_PATH=pictures
```

### Image Features

- **Event-Driven Processing** - Laravel events for decoupled image handling
- **Storage Flexibility** - Support for local and cloud storage (S3)
- **Transactional Attachment** - Atomic file operations with database consistency
- **Direct Access** - Download and inline viewing endpoints for all image types

### Image Features (COMING SOON)

- **Automatic Resizing** - Multiple image sizes generated on upload
- **Format Optimization** - WebP conversion for web optimization

## 🧪 Testing

- **Unit Tests** - Model validation, factory testing, and business logic validation
- **Feature Tests** - API endpoint testing with authentication and authorization
- **Integration Tests** - Cross-model relationship and workflow validation
- **Test Isolation** - Tests use faking prevents external dependencies
- **DRY principle** - Tests use Traits to avoid code repetition

## 🔍 API Documentation

- **OpenAPI** - OpenAPI `api.json` automatically generated via `scramble:export`
- **Swagger** - Interactive API documentation using `api.json`.
- **api-client npm package** - Typescript client package generated via `@openapitools/openapi-generator-cli` to facilitate integration in front-end only applications.

## Developer's Quick Start

### Project Structure

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

### Prerequisites

- **PHP 8.2+** - Modern PHP version with latest features
- **Composer** - PHP dependency management
- **Node.js 20+** - Frontend asset compilation
- **SQLite** (development) or **MariaDB** (production)

### Installation (Windows)

1. **Clone the repository**

    ```powershell
    git clone https://github.com/metanull/inventory-app.git
    Set-Location inventory-app
    ```

2. **Install dependencies**

    ```powershell
    # PHP dependencies
    composer install
    
    # Node.js dependencies
    npm install --no-audit --no-fund

    # Node.js dependencies (SPA demo)
    Push-Location spa
    npm install --no-audit --no-fund
    Pop-Location
    ```

3. **Environment setup**

    ```powershell
    # Create the environment file from template (suitable for development only)
    Copy-Item .env.example .env -Force
    php artisan key:generate

    # (optional/one-time) Download some sample images for database seeding
    .\scripts\download-seed-images.ps1

    # Initialize the database
    php artisan migrate --seed

    ```

7. **Start the server's dev environment**

    ```powershell
    # Start the Laravel server
    composer dev
    ```

8. **Access your application:**

    - **Frontend**: http://localhost:8000/web
    - **SPA Demo**: http://localhost:5174/cli
    - **API Documentation**: http://localhost:8000/docs/api

### 📚 Comprehensive Documentation

For detailed setup instructions, production deployment, and troubleshooting:

- 🚀 **[Complete Deployment Guide](https://metanull.github.io/inventory-app/deployment/)** - Production and development setup
- 💻 **[Development Environment](https://metanull.github.io/inventory-app/development-setup/)** - Local development guide  
- 🔧 **[Configuration Guide](https://metanull.github.io/inventory-app/configuration/)** - Environment and application settings
- 🌐 **[Server Configuration](https://metanull.github.io/inventory-app/server-configuration/)** - Apache/Nginx setup
- 🛠️ **[Testing](https://metanull.github.io/inventory-app/testing)** - Testing


## Frontend developer's Quick Start

As the developer of Front-end application consuming the API you will want to interact with the API.
The most straightforward approach is to use the TypeScript-Axios client library that is automatically generated and published og Github Package.

### Installation

```powershell
npm install @metanull/inventory-app-api-client@latest
```

### Usage

```typescript
import { Configuration, DefaultApi } from '@metanull/inventory-app-api-client';

const api = new DefaultApi(new Configuration({ basePath: 'https://your.api.url' }));
api.addressIndex().then(response => console.log(response.data));
```

### Package Information

- **Package**: [`@metanull/inventory-app-api-client`](https://github.com/metanull/inventory-app/packages)
- **Registry**: [GitHub Packages](https://npm.pkg.github.com/)

## Deployment

### Production Environment

The application is designed for deployment on **Windows Server** environments with the following stack:

- **Web Server**: Apache HTTP 2.4+ with PHP module or PHP-CGI
- **Database**: MariaDB 10.5+ for optimal performance and compatibility
- **PHP**: 8.2+ with required extensions (fileinfo, zip, sqlite3, pdo_sqlite, gd, exif)
- **Storage**: Local filesystem or AWS S3 for image storage

### Environment Configuration

Key environment variables for production:

```env
# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=your-mariadb-host
DB_DATABASE=inventory_production
DB_USERNAME=your-db-user
DB_PASSWORD=your-secure-password

# Image Storage (updated configuration)
UPLOAD_IMAGES_DISK=local_upload_images
UPLOAD_IMAGES_PATH=uploads/images

AVAILABLE_IMAGES_DISK=local_available_images
AVAILABLE_IMAGES_PATH=available/images

PICTURES_DISK=local_pictures
PICTURES_PATH=pictures

# Security
SANCTUM_STATEFUL_DOMAINS=your-frontend-domain.com
SESSION_SECURE_COOKIE=true
```

### Web Server Configuration

The `deployment/` directory contains ready-to-use web server configuration files:

- **`apache.conf`** - Apache virtual host for Linux/Unix systems

These configurations include:
- **Security headers** and SSL/TLS optimization
- **Laravel URL rewriting** and proper routing
- **Static asset caching** and performance optimization
- **Access restrictions** for sensitive directories
- **Production-ready SSL** configuration templates

### CI/CD Pipeline

The project includes a comprehensive **GitHub Actions** workflow for:

- **Automated Testing** - Commits not passing all tests will be rejected
- **Code Quality Checks** - Commits not passing Laravel Pint and eslint checks will be rejected
- **Security Scanning** - Composer audit and CodeQL analysis
- **Documentation & GitHub Pages 📚** - The project automatically generates and maintains comprehensive documentation through **GitHub Pages**:
  - 🌐 **Live Documentation**: [https://metanull.github.io/inventory-app](https://metanull.github.io/inventory-app)
  - 🔄 **CI/CD Integration** - Jekyll builds and deploys automatically
  - 📊 **Commit Tracking** - Complete development history with diff statistics

