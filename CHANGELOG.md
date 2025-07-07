# Changelog

All notable changes to the Inventory Management API project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed

- **TagItem Pivot Table Refactoring**: Simplified many-to-many relationship between Items and Tags
    - **Removed TagItem Model**: Eliminated over-engineered TagItem model in favor of Laravel's standard pivot table approach
    - **Database Schema**: Replaced `tag_items` table with proper Laravel `item_tag` pivot table
        - Follows Laravel naming conventions (alphabetical order: `item_tag`)
        - Uses composite primary key (`item_id`, `tag_id`) instead of separate UUID primary key
        - Maintains foreign key constraints and timestamps
    - **Removed TagItem Infrastructure**: Deleted all TagItem-related files
        - Removed `TagItem` model, factory, seeder, controller, resource, and tests
        - Updated `DatabaseSeeder` to use new `ItemTagSeeder` for pivot table population
        - Cleaned TagItem imports and references from existing tests
    - **Model Relationships**: Updated Item and Tag models to use standard Laravel `belongsToMany` relationships
        - Removed custom pivot model specification
        - Maintained `withTimestamps()` for created_at/updated_at tracking
        - All existing scopes (`forTag`, `withAllTags`, `withAnyTags`, `forItem`) work unchanged
    - **Test Suite Updates**: Fixed all tests to use Eloquent relationship methods instead of TagItem factory
        - Updated 881 tests to use `$item->tags()->attach()` and `$tag->items()->attach()` methods
        - Maintained all existing functionality and test coverage
        - All tests pass with improved performance and simpler code
    - **Enhanced API**: Added quick tag editing endpoint for efficient tag management
        - New `PATCH /api/item/{item}/tags` endpoint for updating item tags without full item update
        - Supports both `attach` and `detach` operations in single request
        - Prevents duplicate tag attachments and handles non-existent detachments gracefully
        - Comprehensive validation for tag UUIDs and existence checks
        - Returns updated item with all relationships loaded
        - Complete test coverage with 15 test cases covering all scenarios
- **Internationalization Refactoring**: Migrated from `*Language` pivot models to `*Translation` models
    - **Translation Models**: Replaced `ContactLanguage`, `ProvinceLanguage`, `LocationLanguage`, `AddressLanguage` with proper translation models
        - New models: `ContactTranslation`, `ProvinceTranslation`, `LocationTranslation`, `AddressTranslation`
        - Changed from many-to-many relationships with pivot tables to one-to-many relationships with dedicated translation tables
        - Updated all main models (`Contact`, `Province`, `Location`, `Address`) to use `translations()` hasMany relationships
    - **Database Schema**: Updated database structure to follow Laravel translation conventions
        - New migration tables: `contact_translations`, `province_translations`, `location_translations`, `address_translations`
        - Removed old pivot tables: `contact_language`, `province_language`, `location_language`, `address_language`
        - Each translation table includes foreign keys to parent model and language
        - Added unique constraints on (model_id, language_id) combinations
    - **API Endpoints**: Added new translation-specific API endpoints
        - `/api/contact-translation`, `/api/province-translation`, `/api/location-translation`, `/api/address-translation`
        - Full CRUD operations for all translation models
        - Proper API resources and controllers for each translation type
        - Updated main model endpoints to return embedded translations
    - **Testing Coverage**: Complete test suite for all translation models
        - Unit tests for factories with proper state methods and model relationships
        - Feature tests for all CRUD operations and anonymous access validation
        - Fixed factory state methods for default context handling
        - All 924 tests pass successfully with 3632 assertions
        - Comprehensive validation of unique constraints and field requirements
    - **Documentation**: Updated API documentation to reflect translation model changes and new endpoints

### Removed

- **Contextualization and Internationalization Models**: Removed complex contextualization system deemed too complex for current requirements (July 6, 2025)
    - **Contextualization Model**: Removed model, controller, resource, factory, seeder, and all related tests
    - **Internationalization Model**: Removed model, controller, resource, factory, seeder, and all related tests
    - **Database Tables**: Added migrations to drop `contextualizations` and `internationalizations` tables
    - **API Endpoints**: Removed all contextualization and internationalization API routes
    - **Model Relationships**: Cleaned up relationships in Item, Detail, Context, and Author models
    - **Documentation**: Updated API documentation and field documentation

### Added

- **Geographic Models**: Complete geographic data management system with internationalization support
    - **Province Model**: New Province model with UUID primary keys, country relationships, and multi-language support
        - Full CRUD API endpoints (`/api/provinces`)
        - Language-specific content via ProvinceTranslation model
        - Factory, seeder, and comprehensive test coverage (40+ tests)
        - Required relationship to Country model
    - **Location Model**: New Location model for geographic locations within provinces
        - Full CRUD API endpoints (`/api/locations`)
        - Language-specific content via LocationTranslation model
        - Relationships to both Country and Province models
        - Factory, seeder, and comprehensive test coverage (40+ tests)
    - **Address Model**: New Address model for detailed address information
        - Full CRUD API endpoints (`/api/addresses`)
        - Language-specific content via AddressTranslation model
        - Relationships to Country, Province, and Location models
        - Factory, seeder, and comprehensive test coverage (40+ tests)
    - **Internationalization Support**: Multi-language pivot tables for all geographic models
        - ProvinceTranslation, LocationTranslation, and AddressTranslation models
        - Language-specific name and description fields
        - API responses include all available language versions
        - Consistent with existing Contact model internationalization patterns
    - **Database Structure**: Six new migrations with proper foreign key constraints
        - Geographic entity tables with UUID primary keys and internal_name fields
        - Language pivot tables with composite primary keys
        - Proper indexing for optimal query performance
        - backward_compatibility column for data migration support
    - **API Resources**: Dedicated API resources for consistent JSON responses
        - Includes language data in responses for internationalization
        - Follows existing Contact model response patterns
        - Proper resource transformation for all geographic models
    - **Complete Test Coverage**: 120+ new tests across unit and feature test suites
        - Factory tests for data generation validation
        - Full CRUD operation testing for all endpoints
        - Authentication and authorization testing
        - Relationship and constraint validation
- **Item and Detail Internationalization and Contextualization**: Complete internationalization and contextualization system for core inventory models
    - **Translation Models**: New ItemTranslation and DetailTranslation models supporting multi-language, multi-context content
        - ItemTranslation model with comprehensive field set: name, alternate_name, description, type, holder, owner, initial_owner, dates, location, dimensions, place_of_production, method_for_datation, method_for_provenance, obtention, bibliography
        - DetailTranslation model with focused field set: name, alternate_name, description
        - Both models support author relationships (author, text_copy_editor, translator, translation_copy_editor)
        - UUID primary keys with foreign key relationships to items/details, languages, contexts, and authors
        - Unique constraints on (model_id, language_id, context_id) combinations to prevent duplicates
        - JSON extra field for extensible metadata storage
    - **Database Schema**: New translation tables following Laravel internationalization conventions
        - item_translations and detail_translations tables with proper indexing
        - Foreign key constraints with cascade delete for parent models and set null for authors
        - Context-aware translations enabling multiple versions per language
        - Backward compatibility support for data migration
    - **Eloquent Relationships**: Enhanced Item and Detail models with translation support
        - hasMany relationships to translation models with eager loading support
        - Helper methods for retrieving default context and contextualized translations
        - Fallback logic for graceful handling of missing translations
        - Scope methods for filtering by language and context
    - **API Endpoints**: Full CRUD REST API for translation management
        - `/api/item-translation` and `/api/detail-translation` endpoints
        - Filtering capabilities by item_id, detail_id, language_id, context_id
        - Default context filtering for simplified queries
        - Comprehensive validation with proper error handling for unique constraint violations
        - Nested relationship loading for complete translation data
    - **Factory and Seeder Support**: Complete test data generation infrastructure
        - ItemTranslationFactory and DetailTranslationFactory with state methods
        - Support for default context, specific language/context, and author configurations
        - ItemTranslationSeeder and DetailTranslationSeeder for sample data generation
        - Integration with existing Item and Detail factories via withoutTranslations states
    - **API Resource Integration**: Enhanced parent model resources with translation data
        - ItemResource and DetailResource now include embedded translations
        - ItemTranslationResource and DetailTranslationResource for dedicated translation endpoints
        - Proper relationship loading with whenLoaded for performance optimization
        - Complete field mapping with relationship data inclusion
    - **Context and Language Integration**: Full integration with existing Context and Language systems
        - Support for default context identification via Context model
        - Language-specific content with ISO 639-1 language codes
        - Context factory enhanced with default() state method for testing
        - Seamless integration with existing geographic translation patterns
- **Code Quality and Development Workflow Enhancements**: Comprehensive development environment improvements
    - **Husky Integration**: Automated Git hooks for code quality enforcement using [Husky](https://typicode.github.io/husky)
    - **lint-staged Implementation**: Efficient code formatting on staged files using [lint-staged](https://www.npmjs.com/package/lint-staged)
    - **Prettier Integration**: Automatic code formatting for JavaScript, TypeScript, CSS, and Markdown using [Prettier](https://prettier.io/)
    - **Stylelint Configuration**: CSS and SCSS linting with [Stylelint](https://stylelint.io/) for consistent stylesheets
    - **Pre-commit Automation**: Automatic linting and formatting of staged files before commits
    - **Enhanced Composer Scripts**: Updated `ci-lint` script to improve CI/CD reliability
- **Internationalization Feature**: Complete multi-language support system for the API
    - **Language-specific Endpoints**: New API endpoints for retrieving content in specific languages
        - `GET /api/internationalization/english` - Retrieve all English content
        - `GET /api/internationalization/default` - Retrieve content in default language
    - **Enhanced Language Model**: Extended Language model with default language management
    - **Automatic Content Filtering**: Language-specific filtering for all major content models (Items, Partners, Projects, etc.)
    - **Multi-language Data Structure**: Support for language-specific content organization
    - **Default Language Scopes**: Efficient queries for default language content across all models
    - **Comprehensive Test Coverage**: 40+ new tests covering internationalization functionality
    - **Language-aware API Resources**: Updated API responses to include language-specific content
- **Enhanced OpenAPI Documentation Generation**: Improved API documentation automation and deployment
    - **Updated Swagger UI**: Upgraded to swagger-ui-dist@5.11.0 for better interactive documentation
    - **Organized Documentation Structure**: Moved API documentation files to `docs/_openapi/` for clarity
    - **Jekyll Integration**: Enhanced integration with Jekyll for seamless documentation deployment
    - **Automated Documentation Pipeline**: Improved `composer ci-openapi-doc` script for consistent API documentation
    - **Better Error Handling**: Enhanced documentation generation with proper error reporting
- **Dependency and Pipeline Management**: Grouped dependency updates and enhanced automation
    - **Enhanced Dependabot Workflow**: Improved auto-merge workflow with admin privileges and GitHub API fallback
    - **Consolidated Updates**: Grouped multiple dependency commits into single PR #180 for cleaner git history
    - **Updated Dependencies**:
        - `tailwindcss` from 4.1.10 to 4.1.11
        - `@tailwindcss/postcss` from 4.1.10 to 4.1.11
        - `@tailwindcss/vite` from 4.1.10 to 4.1.11
        - `nunomaduro/collision` from 8.8.1 to 8.8.2
    - **Improved Automation**: Better reliability for automated dependency updates with branch protection bypass
    - **Git History Optimization**: Reset main branch and consolidated 8 commits for improved maintainability

### Fixed

- **Jekyll Documentation Site Navigation**: Fixed duplicate navigation links in header
    - Resolved improper Liquid template logic in `docs/_includes/header.html`
    - Replaced invalid `if` condition syntax with proper `unless` negation
    - Eliminated duplicate "API Documentation", "Daily Archive" and other page links
    - Improved user experience with clean, semantic navigation HTML
- **MobileAppAuthenticationController HTTP 500 Error**: Fixed critical validation error preventing mobile authentication
    - Resolved invalid Laravel validation rule `'wipe_tokens' => 'boolean|default:false'`
    - Implemented proper validation using `'wipe_tokens' => 'sometimes|boolean'`
    - Enhanced boolean parameter handling with `$request->boolean('wipe_tokens', false)`
    - Prevented "Method Illuminate\Validation\Validator::validateDefault does not exist" exception
- **Database Reset Script**: Enhanced `composer ci-reset` script for reliable database recreation
    - Improved database deletion and recreation process
    - Better error handling for file system operations
    - More reliable test environment preparation
- **Translation Factory State Methods**: Fixed factory test failures for ItemTranslation and DetailTranslation models
    - Added missing `withDefaultContext()` and `forItem()`/`forDetail()` state methods to factories
    - Fixed default context state method to use existing default context instead of creating new ones
    - Corrected index filter tests to properly create multiple translations for the same parent model
    - Enhanced factory imports and method signatures for proper type handling
    - All translation-related tests now pass consistently

### Added (Previous Features)

- **MarkdownService**: Comprehensive content processing service for markdown and HTML conversion
    - **Bidirectional Conversion**: Convert between Markdown ↔ HTML formats with GitHub Flavored Markdown support
    - **Content Validation**: Built-in validation for markdown and HTML content using Laravel validation rules
    - **Security Features**: HTML sanitization, link safety, and protection against malicious content
    - **API Endpoints**: Complete REST API for content processing:
        - `POST /markdown/to-html` - Convert markdown to HTML
        - `POST /markdown/from-html` - Convert HTML to markdown
        - `POST /markdown/validate` - Validate markdown content
        - `POST /markdown/preview` - Generate HTML preview
        - `POST /markdown/is-markdown` - Detect markdown formatting
        - `GET /markdown/allowed-elements` - Get supported elements
    - **Format Detection**: Automatic detection of markdown formatting in text content
    - **Table Support**: Full bidirectional table conversion between HTML and markdown
    - **Code Block Protection**: Proper handling of code syntax and HTML-like content
    - **Laravel Integration**: MarkdownRule for form validation and dependency injection support
    - **Complete Test Coverage**: 690 tests including unit, feature, and integration tests
    - **Library Integration**: CommonMark library with extensions and HTML-to-Markdown converter
- **Tag Management System**: Complete tagging functionality for content organization
    - **Tag Model**: New Tag model with full CRUD operations, factory, seeder, and comprehensive test suite
    - **TagItem Pivot Model**: Many-to-many relationship management between Tags and Items
    - **Enhanced API Endpoints**: Tag-specific endpoints for item-tag relationship management
    - **Scope Methods**: Added tag-based scopes for efficient querying and filtering
    - **Complete Test Coverage**: 40+ new tests covering all tag functionality
- **API Documentation Consolidation**: Enhanced GitHub Pages integration
    - Consolidated GitHub Pages documentation workflow
    - Improved API documentation generation and deployment
    - Enhanced Swagger UI integration with better error handling
- **Contextualization Feature**: Comprehensive contextualized information storage for Items and Details
    - **New Contextualization Model**: UUID-based model with relationships to Context, Item, and Detail entities
    - **Flexible Association**: Each contextualization belongs to either an Item OR a Detail (mutually exclusive)
    - **Context Integration**: Full integration with existing Context system including default context support
    - **Extensible Design**: JSON `extra` field for storing additional unforeseen data
    - **API Endpoints**: Complete REST API with specialized endpoints:
        - Standard CRUD operations (`index`, `show`, `store`, `update`, `destroy`)
        - Default context operations (`GET/POST /contextualizations/default-context`)
        - Filtered endpoints (`/for-items`, `/for-details`)
    - **Comprehensive Validation**: Application-level constraints ensuring exactly one of `item_id` or `detail_id` is set
    - **Enhanced Models**: Added `contextualizations()` relationships to Item, Detail, and Context models
    - **Complete Test Coverage**: 61 new tests covering all functionality including unit and feature tests
    - **Factory & Seeding**: ContextualizationFactory with state methods and seeder for sample data
- **Database Seeders**: Complete seeding system for all key models
    - **PartnerSeeder**: Seeds 5 business partners/organizations with proper country relationships
    - **ItemSeeder**: Seeds 20 main inventory items with partner, country, and project associations
    - **DetailSeeder**: Seeds 50 detailed information records linked to items
    - **TagItemSeeder**: Seeds 30 tag-item relationships for content organization
    - **PictureSeeder**: Seeds 15 item pictures for visual content testing
    - **ImageUploadSeeder**: Seeds 10 uploaded image files for media functionality
    - **AvailableImageSeeder**: Seeds 8 processed/available images for image workflow testing
    - **Enhanced DatabaseSeeder**: Updated to include all missing seeders in proper dependency order
    - **Complete Sample Data**: Full database population enabling comprehensive API testing and development
- **API Documentation Consolidation**: Enhanced GitHub Pages integration
    - Consolidated GitHub Pages documentation workflow
    - Improved API documentation generation and deployment
    - Enhanced Swagger UI integration with better error handling
- **Contextualization Feature**: Comprehensive contextualized information storage for Items and Details
    - **New Contextualization Model**: UUID-based model with relationships to Context, Item, and Detail entities
    - **Flexible Association**: Each contextualization belongs to either an Item OR a Detail (mutually exclusive)
    - **Context Integration**: Full integration with existing Context system including default context support
    - **Extensible Design**: JSON `extra` field for storing additional unforeseen data
    - **API Endpoints**: Complete REST API with specialized endpoints:
        - Standard CRUD operations (`index`, `show`, `store`, `update`, `destroy`)
        - Default context operations (`GET/POST /contextualizations/default-context`)
        - Filtered endpoints (`/for-items`, `/for-details`)
    - **Comprehensive Validation**: Application-level constraints ensuring exactly one of `item_id` or `detail_id` is set
    - **Enhanced Models**: Added `contextualizations()` relationships to Item, Detail, and Context models
    - **Complete Test Coverage**: 61 new tests covering all functionality including unit and feature tests
    - **Factory & Seeding**: ContextualizationFactory with state methods and seeder for sample data
- **Internationalization Feature**: Complete internationalization management system for Laravel application
    - **Core Model**: `Internationalization` model with UUID primary key and relationships to Contextualization, Language, and Author
    - **Database Schema**: Migration with proper foreign key constraints, unique indexes, and timestamp tracking
    - **API Endpoints**: Full CRUD REST API with resource-based responses:
        - `GET /api/internationalizations` - List all internationalizations with pagination
        - `GET /api/internationalizations/{id}` - Show specific internationalization details
        - `POST /api/internationalizations` - Create new internationalization entries
        - `PUT /api/internationalizations/{id}` - Update existing internationalization
        - `DELETE /api/internationalizations/{id}` - Delete internationalization entries
        - `GET /api/internationalizations/by-contextualization/{contextualization}` - Filter by contextualization
        - `GET /api/internationalizations/by-language/{language}` - Filter by language
        - `GET /api/internationalizations/by-author/{author}` - Filter by author
    - **Resource Controller**: Exception handling for unique constraint violations with proper HTTP status codes
    - **Factory and Seeder**: Test data generation and database seeding with proper relationship handling
    - **Complete Test Suite**: 58 comprehensive tests (57 feature + 1 unit) covering all CRUD operations and edge cases
    - **Performance Optimization**: Fast test execution (~15 seconds for full suite) with in-memory SQLite
    - **Code Quality**: PSR-12 compliant, Pint formatted, comprehensive PHPDoc annotations

### Changed

- **Test Suite Enhancement**: Expanded from 453 to 560 tests (1598 assertions)
    - Added comprehensive Tag and TagItem test coverage
    - Enhanced Item scope testing with tag relationships
    - Improved overall test reliability and coverage
- **Git History Organization**: Cleaned and reorganized git history for better maintainability
    - Consolidated GitHub Pages commits into dedicated feature branch
    - Preserved essential Tag functionality commits in main branch
    - Improved linear git history following repository standards

### Fixed

- **HTTP 503 Test Errors**: Resolved persistent HTTP 503 errors in test suite caused by real HTTP requests during testing
    - Enhanced `LoremPicsumImageProvider` to generate valid PNG images in testing environment
    - Added comprehensive `Http::fake()` to all image-related tests (12 test files)
    - Fixed image decoding compatibility with Intervention Image library
- **Picture UpdateTest Correction**: Fixed incorrectly implemented Picture UpdateTest that was testing for non-existent routes instead of proper CRUD operations
    - Replaced route-not-found tests with comprehensive update test suite (8 test methods)
    - Added validation for allowed update fields (internal_name, backward_compatibility, copyright_text, copyright_url)
    - Includes proper HTTP response, database assertion, and validation error tests
- **Route Testing Enhancement**: Enhanced route testing with dual test patterns for better coverage
    - Added exception-based tests for route resolution validation
    - Added HTTP response tests for proper status code validation
    - Applied to ImageUpload, AvailableImage, and Picture models
- **Test Isolation**: Improved test isolation by preventing external dependencies
    - All tests now use proper faking for HTTP, Events, and Storage
    - No real network requests made during test execution
    - Tests execute reliably without external service dependencies
- Fixed Vite manifest missing in CI/CD tests by adding conditional asset loading in Blade views
- Resolved HTTP 503 errors in parallel feature tests by implementing in-memory SQLite database
- Enhanced SQLite configuration with proper timeout and concurrency settings
- Fixed composer.json syntax errors and regenerated autoload files

### Changed

- **Test Suite Performance**: Test execution time improved to ~5.6 seconds with parallel execution
- **Test Coverage**: Increased total tests from 442 to 560 passing tests (1598 assertions)
- **Test Reliability**: All tests now pass consistently without external dependencies
- Reordered GitHub Actions workflow to build assets before running tests
- Updated all Blade views (welcome, app layout, guest layout) with conditional Vite loading
- Enhanced phpunit.xml configuration with VITE_ENABLED=false for testing environment

### Added

- **GitHub Pages Blog Generation**: Automated Jekyll-based documentation system
    - CI/CD workflow automatically generates blog posts for every commit to main branch
    - Jekyll site with responsive minima theme deployed to GitHub Pages
    - Custom layouts with commit navigation, author information, and GitHub links
    - Archive page with searchable commit history and development timeline
    - No local Ruby/Jekyll installation required - fully CI/CD driven
    - Live documentation at: https://metanull.github.io/inventory-app
- **Documentation Scripts**: Optional composer scripts for local Jekyll development
    - `composer docs-install` - Install Jekyll dependencies locally
    - `composer docs-build` - Build Jekyll site locally
    - `composer docs-serve` - Serve site with live reload for development

## [2.4.0] - 2025-06-27

### Added

- **Comprehensive Test Suite**: 560 tests covering all API endpoints and functionality
- **Unit Tests**: Added comprehensive unit tests for all factories and model validation
- **Feature Tests**: Complete API endpoint testing with proper authentication and validation
- **Test Structure**: Organized tests following Laravel best practices with separate directories for each model

### Changed

- **Reorganized Unit Tests**: Updated test structure to follow coding standards with proper directory organization
- **Enhanced Test Coverage**: Improved test coverage across all models and controllers
- **Test Data Management**: Enhanced factories with proper relationships and validation

### Fixed

- **Data Model Alignment**: Fixed validation consistency across application layers
- **Test Route Issues**: Resolved missing route definitions in feature tests
- **Factory Relationships**: Fixed foreign key relationships in test factories

## [2.3.0] - 2025-06-20

### Added

- **Detail Model**: New Detail model with complete CRUD operations, factory, and tests
- **Image Upload Management**: Enhanced image upload processing with proper event handling
- **Custom Faker Provider**: Added LoremPicsumImageProvider for realistic image URLs in tests
- **GitHub Pages Documentation**: Added documentation site with project information

### Changed

- **TailwindCSS Integration**: Re-installed and properly configured TailwindCSS for modern styling
- **README Updates**: Enhanced project documentation with comprehensive information
- **Image Processing**: Improved image upload and processing workflows

### Fixed

- **Image Upload Events**: Fixed image upload event handling and listener processing
- **Picture Model**: Corrected picture model relationships and validation

## [2.2.0] - 2025-06-15

### Added

- **Comprehensive Testing Framework**:
    - Added extensive test coverage for Language, Country, Context, Partner, and Item APIs
    - Implemented proper test factories with relationships
    - Added custom Pint commands for code quality management
- **Enhanced Validation**: Improved validation consistency across controllers and models
- **Database Seeders**: Enhanced seeders with proper foreign key relationships

### Changed

- **Factory Architecture**: Moved foreign key relationships to dedicated factory methods
- **Code Quality**: Implemented Laravel Pint for consistent code formatting
- **Controller Validation**: Enhanced input validation alignment with model constraints

### Fixed

- **Context Store Method**: Fixed ID handling in context creation endpoints
- **Factory Relationships**: Corrected foreign key relationships in test data generation
- **Validation Rules**: Aligned validation rules across models, controllers, and factories

## [2.1.0] - 2025-06-08

### Added

- **Project Model**: Complete Project model with factory, seeder, resource, and controller
- **Advanced Relationships**:
    - Item belongs to Project relationship
    - Partner belongs to Country relationship
    - Enhanced eager loading for optimal performance
- **Scopes and Defaults**:
    - Added model scopes for common queries
    - Implemented default language and context functionality
    - Added enable/disable toggles for projects

### Changed

- **Resource Optimization**: Enhanced API resources with proper relationship loading
- **Database Schema**: Improved foreign key relationships and constraints
- **Code Quality**: Applied Laravel Pint formatting standards across codebase

### Fixed

- **Migration Issues**: Resolved foreign key constraint problems in migrations
- **Resource Cleanliness**: Removed unnecessary commented code from API resources
- **Linting Compliance**: Fixed all code style issues identified by Laravel Pint

## [2.0.0] - 2025-05-15

### Added

- **GitHub Actions CI/CD**:
    - Comprehensive Windows-based CI/CD pipeline
    - Automated testing, linting, and dependency checking
    - Dependabot integration for automated dependency updates
- **Advanced Testing Setup**:
    - Separate testing environment configuration
    - SQLite database for testing isolation
    - Comprehensive test case framework
- **Security Enhancements**:
    - Laravel Sanctum API authentication
    - Proper permission handling in workflows
    - Trust proxy configuration

### Changed

- **PHP Version**: Upgraded to PHP 8.2 minimum requirement
- **Laravel Framework**: Updated to Laravel 12.x for latest features
- **Dependency Management**: Implemented automated dependency updates via Dependabot

### Fixed

- **Composer Dependencies**: Resolved package compatibility issues
- **Testing Environment**: Fixed database configuration for testing
- **GitHub Workflows**: Corrected CI/CD pipeline configuration

## [1.5.0] - 2025-04-20

### Added

- **Picture Upload System**:
    - Complete image upload and processing workflow
    - Automatic image resizing and optimization
    - Event-driven architecture for file processing
    - Picture model with full CRUD operations
- **Enhanced Seeders**:
    - Context seeders with proper relationships
    - API token generation for testing
    - Improved data consistency across environments

### Changed

- **Model Relationships**: Enhanced foreign key relationships between models
- **API Documentation**: Improved Scramble OpenAPI documentation
- **Resource Controllers**: Optimized controller methods following Laravel guidelines

### Fixed

- **Resource Routing**: Corrected leftover resource route issues
- **Relationship Constraints**: Fixed Partner-Item relationship problems
- **Documentation Links**: Added proper OpenAPI documentation links to dashboard

## [1.0.0] - 2025-03-01

### Added

- **Core Inventory Models**:
    - Language model with ISO 639-1 standard compliance
    - Country model with ISO 3166-1 alpha-3 standard compliance
    - Context model for contextual information management
    - Partner model for business entity management
    - Item model for inventory item management
- **RESTful API Architecture**:
    - Complete CRUD operations for all models
    - Laravel Resource classes for consistent API responses
    - Proper HTTP status codes and error handling
- **Database Architecture**:
    - UUID primary keys for scalability
    - Proper foreign key relationships
    - Migration-based schema management
    - Comprehensive indexing strategy
- **API Documentation**:
    - Scramble integration for automatic OpenAPI documentation
    - Interactive API documentation interface
    - Comprehensive endpoint documentation
- **Authentication & Authorization**:
    - Laravel Sanctum for API token authentication
    - Proper middleware configuration
    - Secure API endpoint protection

### Security

- **Input Validation**: Comprehensive validation rules for all endpoints
- **SQL Injection Protection**: Eloquent ORM usage prevents SQL injection
- **CSRF Protection**: Proper CSRF token handling for web routes
- **Rate Limiting**: API rate limiting configuration

## [0.1.0] - 2025-02-01

### Added

- **Initial Laravel Setup**:
    - Laravel 12.x framework installation
    - Laravel Jetstream for authentication scaffolding
    - Laravel Sanctum for API authentication
    - Basic project structure and configuration
- **Development Environment**:
    - Composer dependency management
    - NPM package management
    - Vite for asset compilation
    - Basic styling with TailwindCSS
- **Version Control**:
    - Git repository initialization
    - Basic .gitignore configuration
    - Initial commit with Laravel skeleton

---

## Migration Guide

### From 1.x to 2.x

- **PHP Version**: Ensure PHP 8.2 or higher is installed
- **Laravel Framework**: Update to Laravel 12.x
- **Testing**: Run `php artisan migrate --env=testing` for test database setup
- **Dependencies**: Run `composer update` to update all dependencies

### From 0.x to 1.x

- **Database**: Run migrations to set up core inventory models
- **Seeders**: Execute seeders to populate initial data (countries, languages)
- **API Documentation**: Access OpenAPI documentation at `/docs/api`
- **Authentication**: Generate API tokens for testing purposes

---

## Development Standards

### Code Quality

- **PSR-12 Compliance**: All code follows PSR-12 coding standards
- **Laravel Pint**: Automated code formatting and style checking
- **Comprehensive Testing**: 560+ tests covering all functionality
- **Type Safety**: Proper type hints and return types throughout codebase

### Architecture Principles

- **N-Tier Architecture**: Clear separation between management and consultation layers
- **RESTful Design**: Consistent REST API patterns across all endpoints
- **Event-Driven**: Use of Laravel events for decoupled functionality
- **UUID Standards**: Consistent use of UUIDs for primary keys (except User, Country, Language)

### Security Standards

- **Input Validation**: All inputs validated using Laravel request validation
- **Authentication**: Sanctum-based API authentication
- **Authorization**: Proper middleware and policy implementation
- **SQL Injection Prevention**: Exclusive use of Eloquent ORM for database operations

---

## Contributors

- **Pascal Havelange** - Project Author and Lead Developer
- **Museum With No Frontiers** - Project Sponsor and Requirements Provider

---

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## Links

- **Documentation**: [GitHub Pages](https://metanull.github.io/inventory-app/)
- **API Documentation**: Available at `/docs/api` when running the application
- **Repository**: [GitHub](https://github.com/metanull/inventory-app)
- **Issues**: [GitHub Issues](https://github.com/metanull/inventory-app/issues)
- **Project Management**: [MWNF Jira](https://mwnf.atlassian.net/jira/software/c/projects/MWNF/boards/2)
- **Documentation Wiki**: [MWNF Confluence](https://mwnf.atlassian.net/wiki)
