# Inventory Management API

[![CodeQL](https://github.com/metanull/inventory-app/actions/workflows/github-code-scanning/codeql/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/github-code-scanning/codeql) [![Laravel](https://github.com/metanull/inventory-app/actions/workflows/laravel.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/laravel.yml) [![Dependabot Updates](https://github.com/metanull/inventory-app/actions/workflows/dependabot/dependabot-updates/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/dependabot/dependabot-updates)

The **Inventory Management API** (inventory-app) is a RESTful API designed to manage the content of the Museum With No Frontiers' inventory database. This application serves as the management layer in a modern N-tier architecture, replacing legacy systems with a scalable, maintainable, and secure solution.

## Table of Contents

- [Project Overview](#project-overview)
- [Why N-Tier Architecture?](#why-n-tier-architecture)
- [Data Model and Features](#data-model-and-features)
- [Quick Start](#quick-start)
- [Deployment](#deployment)
- [Technology Stack](#technology-stack)
- [Author](#author)
- [Change Log](#change-log)
- [References](#references)

## Project Overview

This API is part of a broader modernization effort for Museum With No Frontiers. The new architecture consists of:

- **Management REST API** (this application): Provides secure endpoints for managing and updating the inventory database.
- **Public Consultation REST API**: Grants controlled, read-only access to inventory data for public-facing applications.
- **Client-side Web Applications**: Deployed separately, these applications interact with the consultation API to present data to end users.

## Why N-Tier Architecture?

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
- Part of a Project (exhibition or collection)
- Tracks type, internal naming, and legacy system compatibility

**Partner** 🏛️ - Museums, institutions, or organizations managing inventory items
- Has a primary Country for institutional location
- Can own multiple Items
- Supports organizational hierarchy and partnerships

**Project** 🎯 - Collections, exhibitions, or thematic groupings
- Has a primary Context for categorization
- Supports multiple Languages for internationalization
- Includes launch dates and enable/disable functionality
- Can contain multiple Items

**Context** 📂 - Categorical or thematic organization system
- Provides hierarchical content organization
- Supports default context selection
- Enables flexible content categorization

#### Supporting Models

**Picture** 🖼️ - Image management with automatic processing
- Event-driven upload and resizing workflow
- Supports multiple image formats and sizes
- Integrated with AWS S3 or local storage

**Detail** 📋 - Extended metadata and detailed information
- Flexible schema for additional item properties
- JSON-based storage for complex data structures

**ImageUpload** 📤 - Upload tracking and processing status
- Monitors image processing workflows
- Tracks upload success/failure states

**AvailableImage** 🎨 - Image availability and accessibility management
- Controls image visibility and access permissions
- Manages image versions and formats

### Key Features

#### 🔐 Authentication & Security
- **Laravel Sanctum** - Token-based API authentication
- **Role-based Access Control** - Granular permission management
- **Input Validation** - Comprehensive validation for all endpoints
- **SQL Injection Prevention** - Eloquent ORM exclusive usage

#### 🌐 RESTful API Architecture
- **Complete CRUD Operations** - Full Create, Read, Update, Delete functionality
- **Resource Controllers** - Consistent API response formatting
- **HTTP Status Codes** - Proper REST status code implementation
- **Pagination Support** - Efficient handling of large datasets

#### 📊 Advanced Query Features
- **Model Scopes** - Predefined query filters for common operations
  - `Project::enabled()` - Get all enabled projects
  - `Language::default()` - Get default language
  - `Context::default()` - Get default context
- **Eager Loading** - Optimized database queries with relationship preloading
- **Search Capabilities** - Full-text search across relevant fields

#### 🔄 Custom Endpoints
Beyond standard REST operations, the API provides specialized endpoints:

```http
# Default Management
GET    /api/language/default          # Get default language
PATCH  /api/language/{id}/default     # Set language as default
GET    /api/context/default           # Get default context  
PATCH  /api/context/{id}/default      # Set context as default

# Project Management
GET    /api/project/enabled           # Get all enabled projects
PATCH  /api/project/{id}/launched     # Mark project as launched
PATCH  /api/project/{id}/enabled      # Enable/disable project

# Specialized Access
GET    /api/language/english          # Get English language specifically
```

#### 📱 Image Processing Pipeline
- **Automatic Resizing** - Multiple image sizes generated on upload
- **Format Optimization** - WebP conversion for web optimization
- **Event-Driven Processing** - Laravel events for decoupled image handling
- **Storage Flexibility** - Support for local and cloud storage (S3)

#### 🧪 Comprehensive Testing
- **453+ Tests** - Complete test coverage across all functionality with 1163 assertions
- **Unit Tests** - Model validation, factory testing, and business logic validation
- **Feature Tests** - Full API endpoint testing with authentication and authorization
- **Integration Tests** - Cross-model relationship and workflow validation
- **Parallel Testing** - Optimized for CI/CD performance (~5.6 seconds execution)
- **Test Isolation** - HTTP/Event/Storage faking prevents external dependencies
- **100% Reliability** - All tests pass consistently without network dependencies

#### 📋 Data Integrity
- **Foreign Key Constraints** - Enforced referential integrity
- **Validation Rules** - Consistent validation across all layers
- **Backward Compatibility** - Support for legacy system integration
- **UUID Consistency** - Reliable unique identification across distributed systems

#### 🔍 API Documentation
- **OpenAPI/Swagger** - Interactive API documentation via Scramble
- **Automatic Generation** - Documentation generated from code annotations
- **Live Testing** - In-browser API testing capabilities
- **Comprehensive Examples** - Request/response examples for all endpoints

### API Endpoints Overview

The API provides full REST functionality for all models:

| Resource | Endpoints | Special Features |
|----------|-----------|------------------|
| **Countries** | Standard CRUD + ISO code lookups | ISO 3166-1 alpha-3 compliance |
| **Languages** | Standard CRUD + default management | ISO 639-1 compliance, English shortcut |
| **Contexts** | Standard CRUD + default management | Hierarchical organization |
| **Partners** | Standard CRUD + country relationships | Institution management |
| **Projects** | Standard CRUD + launch/enable controls | Project lifecycle management |
| **Items** | Standard CRUD + complex relationships | Central inventory management |
| **Pictures** | Standard CRUD + upload processing | Event-driven image processing |
| **Details** | Standard CRUD + metadata management | Flexible schema support |

All endpoints support:
- **JSON Request/Response** - Consistent data format
- **Authentication** - Sanctum token-based security  
- **Validation** - Comprehensive input validation
- **Error Handling** - Structured error responses
- **Rate Limiting** - API abuse prevention

## Quick Start

### Prerequisites
- **PHP 8.2+** - Modern PHP version with latest features
- **Composer** - PHP dependency management
- **Node.js 20+** - Frontend asset compilation
- **SQLite** (development) or **MariaDB** (production)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/metanull/inventory-app.git
   cd inventory-app
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database setup**
   ```bash
   php artisan migrate --seed
   ```

6. **Build frontend assets**
   ```bash
   npm run build
   ```

7. **Start development server**
   ```bash
   php artisan serve
   ```

### API Documentation

Once running, access the interactive API documentation at:
- **Local Development**: `http://localhost:8000/docs/api`
- **Swagger UI**: Complete API documentation with live testing capabilities

### Testing

The application features a robust and comprehensive test suite designed for reliability and performance:

#### Test Suite Overview
- **453 Tests** - Complete coverage across all API endpoints and functionality
- **1163 Assertions** - Thorough validation of application behavior
- **~5.6 seconds** - Fast execution time with parallel processing
- **100% Reliability** - All tests pass consistently without external dependencies

#### Test Categories
```bash
# Complete test suite
php artisan test                    # All 453 tests

# Parallel execution for CI/CD
php artisan test --parallel         # Optimized for faster execution

# Specific test types
php artisan test tests/Unit         # Unit tests (factories, models)
php artisan test tests/Feature      # Feature tests (API endpoints)
php artisan test tests/Integration  # Integration tests (relationships)

# Coverage reporting
php artisan test --coverage
```

#### Test Features
- **HTTP Isolation** - All tests use `Http::fake()` to prevent real network requests
- **Event Faking** - Proper event isolation with `Event::fake()` for async operations
- **Storage Faking** - File system isolation with `Storage::fake()` for uploads
- **Database Isolation** - Each test uses fresh database state with `RefreshDatabase`
- **Image Processing** - Custom faker provider generates valid test images
- **Authentication Testing** - Complete user authentication and authorization testing
- **Validation Testing** - Comprehensive input validation and error handling tests

### Code Quality

Maintain code standards:
```bash
# Code formatting
./vendor/bin/pint

# Code analysis  
./vendor/bin/pint --bail

# Pre-commit checks
composer ci-before-pull-request
```

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

# Image Storage (optional S3)
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-aws-key
AWS_SECRET_ACCESS_KEY=your-aws-secret
AWS_DEFAULT_REGION=your-region
AWS_BUCKET=your-bucket-name

# Security
SANCTUM_STATEFUL_DOMAINS=your-frontend-domain.com
SESSION_SECURE_COOKIE=true
```

### CI/CD Pipeline

The project includes a comprehensive **GitHub Actions** workflow for:

- ✅ **Automated Testing** - 453+ tests with 1163 assertions, ~5.6s execution time
- ✅ **Code Quality Checks** - Laravel Pint formatting validation
- ✅ **Security Scanning** - Composer audit and CodeQL analysis
- ✅ **Dependency Updates** - Automated Dependabot integration
- ✅ **Build Verification** - Asset compilation and validation

### Performance Considerations

- **Database Indexing** - Optimized indexes for common queries
- **Eager Loading** - Relationship preloading to prevent N+1 queries
- **Caching** - Redis/Memcached support for session and query caching
- **Image Optimization** - WebP conversion and multiple size generation
- **API Rate Limiting** - Configurable rate limits for API endpoints
