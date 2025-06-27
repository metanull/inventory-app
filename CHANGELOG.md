# Changelog

All notable changes to the Inventory Management API project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Fixed
- Fixed Vite manifest missing in CI/CD tests by adding conditional asset loading in Blade views
- Resolved HTTP 503 errors in parallel feature tests by implementing in-memory SQLite database
- Enhanced SQLite configuration with proper timeout and concurrency settings
- Fixed composer.json syntax errors and regenerated autoload files

### Changed
- Reordered GitHub Actions workflow to build assets before running tests
- Updated all Blade views (welcome, app layout, guest layout) with conditional Vite loading
- Enhanced phpunit.xml configuration with VITE_ENABLED=false for testing environment

## [2.4.0] - 2025-06-27

### Added
- **Comprehensive Test Suite**: 442 tests covering all API endpoints and functionality
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
- **Comprehensive Testing**: 442+ tests covering all functionality
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
