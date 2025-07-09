# Inventory Management API

[![CodeQL](https://github.com/metanull/inventory-app/actions/workflows/github-code-scanning/codeql/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/github-code-scanning/codeql) [![Composer+Phpunit+Pint](https://github.com/metanull/inventory-app/actions/workflows/laravel.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/laravel.yml) [![Dependabot](https://github.com/metanull/inventory-app/actions/workflows/dependabot/dependabot-updates/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/dependabot/dependabot-updates)[![GitHub Pages](https://github.com/metanull/inventory-app/actions/workflows/github-pages.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/github-pages.yml)

---

**🌍 Check out our github page** [https://metanull.github.io/inventory-app/](https://metanull.github.io/inventory-app/)

---

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

**Collection** 📚 - Organizational groupings for museum items with translation support

- Contains multiple Items through collection_id foreign key
- Supports multi-language translations via CollectionTranslation model
- Partner relationships with contribution levels (Partner, Associated Partner, Minor Contributor)
- Has default Language and Context for display purposes
- Full CRUD API endpoints with comprehensive validation
- UUID primary key with proper relationships and indexing

**Gallery** 🖼️ - Flexible polymorphic collections for both Items and Details

- Polymorphic many-to-many relationships with Items and Details via Galleryable model
- Supports multi-language translations via GalleryTranslation model
- Partner relationships with contribution levels (Partner, Associated Partner, Minor Contributor)
- Has default Language and Context for display purposes
- Full CRUD API endpoints with comprehensive validation
- UUID primary key with proper relationships and indexing
- Flexible content organization allowing mixed Item and Detail collections

**Exhibition** 🎨 - Hierarchical theme-based picture galleries with comprehensive translation support

- Organizes Pictures from Items and Details into hierarchical Themes
- Two-level theme structure: Main Themes → Subthemes (2-level depth maximum)
- Supports multi-language translations via ExhibitionTranslation model
- Theme translations via ThemeTranslation model (title, description, introduction)
- Partner relationships with contribution levels (Partner, Associated Partner, Minor Contributor)
- Has default Language and Context for display purposes
- Full CRUD API endpoints with comprehensive validation
- UUID primary key with proper relationships and indexing
- Polymorphic picture attachments supporting both Item and Detail pictures

**Context** 📂 - Categorical or thematic organization system

- Provides hierarchical content organization
- Supports default context selection
- Enables flexible content categorization

#### Supporting Models

**Picture** 🖼️ - Polymorphic image attachment system

- Polymorphic relationships with Items, Details, and Partners
- Automatic file management and metadata tracking
- Support for multiple image formats with validation
- Transactional attachment from AvailableImage pool
- Direct download and inline viewing capabilities

**Detail** 📋 - Extended metadata and detailed information

- Flexible schema for additional item properties
- JSON-based storage for complex data structures
- Supports image attachments via polymorphic Pictures

**Tag** 🏷️ - Content tagging and categorization system

- Flexible tagging system for items
- Supports hierarchical and multi-dimensional categorization
- Many-to-many relationships with Items via standard Laravel pivot table

**ImageUpload** 📤 - Upload tracking and processing status

- Monitors image processing workflows
- Tracks upload success/failure states
- Creates AvailableImage records upon successful processing

**AvailableImage** 🎨 - Processed image pool for attachment

- Temporary storage for processed images awaiting attachment
- Automatic cleanup when attached to models as Pictures
- Download and preview capabilities before attachment
- Support for multiple image formats and sizes

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

# Tag Management
GET    /api/item/{id}/tags            # Get all tags for an item
GET    /api/tag/{id}/items            # Get all items with a specific tag
POST   /api/tag-item                  # Create tag-item relationship
DELETE /api/tag-item/{id}             # Remove tag-item relationship

# Collection Management
GET    /api/collection                # Get all collections with relationships
GET    /api/collection/{id}           # Get specific collection with translations
POST   /api/collection                # Create new collection
PUT    /api/collection/{id}           # Update existing collection
DELETE /api/collection/{id}           # Delete collection

# Gallery Management
GET    /api/gallery                   # Get all galleries with relationships
GET    /api/gallery/{id}              # Get specific gallery with translations
POST   /api/gallery                   # Create new gallery
PUT    /api/gallery/{id}              # Update existing gallery
DELETE /api/gallery/{id}              # Delete gallery

# Exhibition Management
GET    /api/exhibition-translation    # Get all exhibition translations with filtering
GET    /api/exhibition-translation/{id} # Get specific exhibition translation
POST   /api/exhibition-translation    # Create new exhibition translation
PUT    /api/exhibition-translation/{id} # Update existing exhibition translation
DELETE /api/exhibition-translation/{id} # Delete exhibition translation

# Theme Management
GET    /api/theme-translation         # Get all theme translations with filtering
GET    /api/theme-translation/{id}    # Get specific theme translation
POST   /api/theme-translation         # Create new theme translation
PUT    /api/theme-translation/{id}    # Update existing theme translation
DELETE /api/theme-translation/{id}    # Delete theme translation

# Picture Attachment System
POST   /api/picture/attach-to-item/{item}        # Attach AvailableImage to Item
POST   /api/picture/attach-to-detail/{detail}    # Attach AvailableImage to Detail
POST   /api/picture/attach-to-partner/{partner}  # Attach AvailableImage to Partner

# Picture Detachment System
DELETE /api/picture/{picture}/detach-from-item/{item}       # Detach Picture from Item
DELETE /api/picture/{picture}/detach-from-detail/{detail}   # Detach Picture from Detail
DELETE /api/picture/{picture}/detach-from-partner/{partner} # Detach Picture from Partner

# Picture Management
GET    /api/picture/{id}/download                # Download attached picture
GET    /api/picture/{id}/view                    # View attached picture inline

# Image Management
GET    /api/available-image/{id}/download        # Download available image
GET    /api/available-image/{id}/view            # View available image inline
POST   /api/image-upload                         # Upload new image for processing

# Markdown Processing
POST   /api/markdown/to-html          # Convert markdown to HTML
POST   /api/markdown/from-html        # Convert HTML to markdown
POST   /api/markdown/validate         # Validate markdown content
POST   /api/markdown/preview          # Generate HTML preview
POST   /api/markdown/is-markdown      # Detect markdown formatting
GET    /api/markdown/allowed-elements # Get supported HTML elements

# Mobile Authentication
POST   /api/mobile/acquire-token      # Acquire authentication token
GET    /api/mobile/wipe               # Wipe user tokens

# Specialized Access
GET    /api/language/english          # Get English language specifically
```

# Image Processing Pipeline

The application features a sophisticated image processing and attachment system:

#### Image Upload and Processing Workflow

1. **Upload**: Images are uploaded via `POST /api/image-upload` and processed asynchronously
2. **Processing**: Background events resize, validate, and optimize images
3. **Available Pool**: Successfully processed images become `AvailableImage` records
4. **Attachment**: Images are attached to models via transactional operations:
    - `POST /api/picture/attach-to-item/{item}` - Attach to Items
    - `POST /api/picture/attach-to-detail/{detail}` - Attach to Details
    - `POST /api/picture/attach-to-partner/{partner}` - Attach to Partners
5. **Management**: Attached images become `Picture` records with full CRUD operations
6. **Detachment**: Pictures can be detached and converted back to AvailableImages:
    - `DELETE /api/picture/{picture}/detach-from-item/{item}` - Detach from Items
    - `DELETE /api/picture/{picture}/detach-from-detail/{detail}` - Detach from Details
    - `DELETE /api/picture/{picture}/detach-from-partner/{partner}` - Detach from Partners

#### Image Storage Configuration

The application uses a flexible storage configuration system with clear separation:

```bash
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

#### Image Features

- **Automatic Resizing** - Multiple image sizes generated on upload
- **Format Optimization** - WebP conversion for web optimization
- **Event-Driven Processing** - Laravel events for decoupled image handling
- **Storage Flexibility** - Support for local and cloud storage (S3)
- **Transactional Attachment** - Atomic file operations with database consistency
- **Direct Access** - Download and inline viewing endpoints for all image types

#### 🧪 Comprehensive Testing

- **1098+ Tests** - Complete test coverage across all functionality with 4544+ assertions
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

| Resource                  | Endpoints                               | Special Features                                  |
| ------------------------- | --------------------------------------- | ------------------------------------------------- |
| **Countries**             | Standard CRUD + ISO code lookups        | ISO 3166-1 alpha-3 compliance                     |
| **Languages**             | Standard CRUD + default management      | ISO 639-1 compliance, English shortcut            |
| **Contexts**              | Standard CRUD + default management      | Hierarchical organization                         |
| **Partners**              | Standard CRUD + country relationships   | Institution management                            |
| **Projects**              | Standard CRUD + launch/enable controls  | Project lifecycle management                      |
| **Items**                 | Standard CRUD + complex relationships   | Central inventory management                      |
| **Tags**                  | Standard CRUD + relationship management | Flexible content tagging                          |
| **Pictures**              | Standard CRUD + polymorphic attachments | Attach to Items/Details/Partners, file management |
| **Details**               | Standard CRUD + metadata management     | Flexible schema support, image attachments        |
| **Contextualizations**    | Standard CRUD + context filtering       | Context-content association management            |
| **AvailableImages**       | Standard CRUD + image downloads/views   | Processed image pool, attachment workflow         |
| **Markdown**              | Content processing utilities            | Markdown ↔ HTML conversion and validation        |
| **Internationalization**  | Language-specific content               | Multi-language content retrieval                  |
| **Mobile Authentication** | Token management                        | Mobile app authentication and token handling      |

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

### TypeScript API Client

The project automatically generates a TypeScript-Axios client library from the OpenAPI specification:

#### Installation

```bash
npm install @metanull/inventory-app-api-client@latest
```

#### Usage

```typescript
import { Configuration, DefaultApi } from '@metanull/inventory-app-api-client';

const api = new DefaultApi(new Configuration({ basePath: 'https://your.api.url' }));
api.addressIndex().then(response => console.log(response.data));
```

#### Package Information

- **Package**: [`@metanull/inventory-app-api-client`](https://github.com/metanull/inventory-app/packages)
- **Registry**: [GitHub Packages](https://npm.pkg.github.com/)
- **Documentation**: Auto-generated client documentation available in `/api-client/docs/`

#### Generation & Publishing

```powershell
# Generate client from OpenAPI spec
.\scripts\generate-api-client.ps1

# Publish to GitHub Packages
.\scripts\publish-api-client.ps1 -Credential (Get-Credential -Message "GitHub PAT")
```

The client is automatically versioned and published to GitHub Packages, ensuring type-safe API consumption with comprehensive documentation.

### Testing

The application features a robust and comprehensive test suite designed for reliability and performance:

#### Test Suite Overview

- **560 Tests** - Complete coverage across all API endpoints and functionality
- **1598 Assertions** - Thorough validation of application behavior
- **~5.6 seconds** - Fast execution time with parallel processing
- **100% Reliability** - All tests pass consistently without external dependencies

#### Test Categories

```bash
# Complete test suite
php artisan test                    # All 560 tests

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

### Enhanced Composer Commands

The application provides enhanced composer commands that accept additional parameters for flexible development workflows:

#### Testing Commands

```bash
# Standard test execution
composer ci-test

# Pass additional arguments using environment variable
$env:COMPOSER_ARGS="--filter Integration"; composer ci-test

# Common test filtering examples
$env:COMPOSER_ARGS="--filter Picture"; composer ci-test
$env:COMPOSER_ARGS="--filter DetachFromItemTest"; composer ci-test
$env:COMPOSER_ARGS="--group=integration"; composer ci-test
$env:COMPOSER_ARGS="--testsuite=Feature"; composer ci-test

# Helper command for filter-based testing
composer ci-test:filter "YourTestClass"
```

#### Linting Commands

```bash
# Standard linting
composer ci-lint

# Pass additional arguments using environment variable
$env:COMPOSER_ARGS="--test"; composer ci-lint
$env:COMPOSER_ARGS="--config=custom-pint.json"; composer ci-lint
$env:COMPOSER_ARGS="--verbose"; composer ci-lint

# Helper command for lint with arguments
composer ci-lint:with-args --test --verbose
```

#### Environment Variable Usage

The enhanced commands support the `COMPOSER_ARGS` environment variable to pass additional parameters:

```powershell
# PowerShell examples
$env:COMPOSER_ARGS="--filter IntegrationTest"
composer ci-test

# Or inline
$env:COMPOSER_ARGS="--coverage"; composer ci-test
```

This approach allows the composer commands to act as shortcuts to the underlying artisan commands while maintaining full parameter flexibility.

### Code Quality

Maintain code standards:

```bash
# Code formatting
./vendor/bin/pint

# Code analysis
./vendor/bin/pint --bail

# Pre-commit checks
composer ci-before:pull-request
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

### CI/CD Pipeline

The project includes a comprehensive **GitHub Actions** workflow for:

- ✅ **Automated Testing** - 560+ tests with 1598 assertions, ~5.6s execution time
- ✅ **Code Quality Checks** - Laravel Pint formatting validation
- ✅ **Security Scanning** - Composer audit and CodeQL analysis
- ✅ **Dependency Updates** - Automated Dependabot integration
- ✅ **Build Verification** - Asset compilation and validation

#### Enhanced CI Scripts

The project includes enhanced PowerShell scripts in the `scripts/` directory for improved CI/CD operations:

- **`ci-test.ps1`** - Enhanced test execution with argument passing support
- **`ci-test-with-filter.ps1`** - Specialized test filtering capabilities
- **`ci-lint.ps1`** - Enhanced linting with configurable options
- **`ci-lint-with-args.ps1`** - Flexible linting with argument support

These scripts provide:

- Better error handling and reporting
- Flexible argument passing through environment variables
- Improved developer experience with clearer output
- Support for specialized testing workflows (filtering, grouping, etc.)
- Enhanced code quality validation with configurable rules

### Documentation & GitHub Pages 📚

The project automatically generates and maintains comprehensive documentation through **GitHub Pages**:

- 🌐 **Live Documentation**: [https://metanull.github.io/inventory-app](https://metanull.github.io/inventory-app)
- 📝 **Automated Blog Posts** - Every commit to `main` generates a detailed blog post
- 🔄 **CI/CD Integration** - Jekyll builds and deploys automatically
- 📊 **Commit Tracking** - Complete development history with diff statistics
- 🎨 **Responsive Design** - Clean, mobile-friendly interface with search and navigation

The documentation includes:

- Development progress tracking through commit-based blog posts
- Code changes and statistics for each commit
- Author information and commit timestamps
- Links to GitHub commits and pull requests
- Searchable archive of all development activities

> **Note**: GitHub Pages generation is fully automated - no local Ruby or Jekyll installation required!

### Performance Considerations

- **Database Indexing** - Optimized indexes for common queries
- **Eager Loading** - Relationship preloading to prevent N+1 queries
- **Caching** - Redis/Memcached support for session and query caching
- **Image Optimization** - WebP conversion and multiple size generation
- **API Rate Limiting** - Configurable rate limits for API endpoints
