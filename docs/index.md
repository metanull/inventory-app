---
layout: default
title: Home
nav_order: 1
---

[![PHP 8.2+](https://img.shields.io/badge/php-8.2+-777bb3.svg?logo=php&logoColor=white&labelColor=555555)](https://php.net)
[![Laravel 12](https://img.shields.io/badge/laravel-12-f05340.svg?logo=laravel&logoColor=ffffff&labelColor=6c6c6c)](https://laravel.com)
[![github](https://img.shields.io/badge/Source-github-151013.svg?logo=github&logoColor=white&labelColor=555555)](https://github.com/metanull/inventory-app)
[![LICENSE](https://img.shields.io/badge/license-MIT-428f7e.svg?logo=open%20source%20initiative&logoColor=white&labelColor=555555)](https://github.com/metanull/inventory-app/blob/main/LICENSE)
[![Continuous Integration](https://github.com/metanull/inventory-app/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/continuous-integration.yml)
[![Build](https://github.com/metanull/inventory-app/actions/workflows/build.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/build.yml)
[![Deploy](https://github.com/metanull/inventory-app/actions/workflows/deploy.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/deploy.yml)
[![Documentation](https://github.com/metanull/inventory-app/actions/workflows/continuous-deployment_github-pages.yml/badge.svg)](https://metanull.github.io/inventory-app)
[![API Client](https://github.com/metanull/inventory-app/actions/workflows/publish-api-client.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/publish-api-client.yml)
[![CodeQL](https://github.com/metanull/inventory-app/actions/workflows/github-code-scanning/codeql/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/github-code-scanning/codeql)
[![Dependabot](https://github.com/metanull/inventory-app/actions/workflows/dependabot/dependabot-updates/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/dependabot/dependabot-updates)

# Inventory Management API

A Laravel-based REST API for managing museum inventory data with multi-language translation support, image processing workflows, and role-based access control.

## Quick Start

New to this project? Start here:

1. [Development Setup]({{ '/deployment/development-setup' | relative_url }}) - Set up your local environment
2. [API Documentation]({{ '/api/' | relative_url }}) - Explore the API endpoints
3. [Database Models]({{ '/models/' | relative_url }}) - Understand the data structure
4. [Contributing]({{ '/development/contributing' | relative_url }}) - Guidelines for contributing

## Technology Stack

- **Backend**: PHP 8.2+ with Laravel 12
- **Database**: MariaDB (production), SQLite (development/testing)
- **Authentication**: Laravel Sanctum for API token management
- **Documentation**: OpenAPI 3.0 with Swagger UI
- **Testing**: PHPUnit with comprehensive test coverage
- **CI/CD**: GitHub Actions for automated testing and deployment

## Project Architecture

This API is part of the modernization effort for Museum With No Frontiers:

- **Management REST API** (this application): Secure endpoints for managing inventory database
- **Public Consultation REST API**: Read-only access for public-facing applications
- **Client-side Web Applications**: Interact with the consultation API to present data

## This very website

This documentation website is built with Jekyll and automatically generated from multiple sources. Learn about the site architecture, custom plugins, and how content is generated from scripts, Git commits, and API specifications.

{: .note }

> Read more about it in [Development / Documentation Site]({{ '/development/documentation-site/' | relative_url }})

## Documentation Sections

### [Blade/Livewire Frontend]({{ '/frontend-blade/' | relative_url }})

{: .note }

> This is the main user interface, it is a Blade/Livewire frontend.

{: .important }
It is a **Server rendered frontend**.

### [Backend Guidelines]({{ '/guidelines/' | relative_url }})

Development guidelines covering coding standards, API integration, and best practices.

### [API Documentation]({{ '/api/' | relative_url }})

Interactive API documentation with Swagger UI and OpenAPI specification.

### [Database Models]({{ '/models/' | relative_url }})

Complete reference for all database models, their properties, and relationships.

### [Development]({{ '/development/' | relative_url }})

Contributing guidelines, testing strategies, and project history.

### [Deployment Guide]({{ '/deployment/' | relative_url }})

Production and development environment setup instructions.

### [Vue.js Sample Frontend]({{ '/frontend-vue-sample/' | relative_url }})

Sample Vue.js application demonstrating API integration.

{: .note }

> This documents a sample Vue.js client application. The main user interface is the Blade/Livewire frontend.

---

## Additional Resources

- [GitHub Repository](https://github.com/metanull/inventory-app) - Source code and issue tracking
- [GitHub Issues](https://github.com/metanull/inventory-app/issues) - Bug reports and feature requests
- [GitHub Page](https://metanull.github.io/inventory-app/) - _This_ documentation website
- [Development Archive]({{ '/development/archive' | relative_url }}) - Complete commit history

## License

This project is licensed under the MIT License - see the [LICENSE](https://github.com/metanull/inventory-app/blob/main/LICENSE) file for details.

---

_Last updated: {{ site.time | date: "%B %d, %Y" }}_
