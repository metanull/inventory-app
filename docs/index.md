---
layout: default
title: Home
nav_order: 1
---

[![Continuous Integration](https://github.com/metanull/inventory-app/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/continuous-integration.yml)
[![Continuous Deployment](https://github.com/metanull/inventory-app/actions/workflows/continuous-deployment.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/continuous-deployment.yml)
[![Continuous Deployment to GitHub Pages](https://github.com/metanull/inventory-app/actions/workflows/continuous-deployment_github-pages.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/continuous-deployment_github-pages.yml)
[![Publish npm github package @metanull/inventory-app-api-client](https://github.com/metanull/inventory-app/actions/workflows/publish-npm-github-package.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/publish-npm-github-package.yml)
[![CodeQL](https://github.com/metanull/inventory-app/actions/workflows/github-code-scanning/codeql/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/github-code-scanning/codeql)
[![Dependabot](https://github.com/metanull/inventory-app/actions/workflows/dependabot/dependabot-updates/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/dependabot/dependabot-updates)

# Inventory Management API

{: .highlight }

> The **Inventory Management API** (inventory-app) is a RESTful API designed to manage the content of the Museum With No Frontiers' inventory database. This application serves as the management layer in a modern N-tier architecture, replacing legacy systems with a scalable, maintainable, and secure solution.

## ✨ Features

- 🚀 **RESTful API** for inventory management with complete CRUD operations
- 🔒 **User Authentication** and JWT token management
- 🛡️ **Database-agnostic** Eloquent models with UUID primary keys
- 📊 **Clean, maintainable** codebase following Laravel best practices
- 📚 **Automated API Documentation** with interactive testing capabilities
- ✅ **Comprehensive Test Suite**: 924 tests with 3632 assertions (100% reliable, high-speed execution)
- 🎨 **Polymorphic Picture System**: Attach images to Items, Details, and Partners with automatic file management
- 🔧 **Advanced Query Features**: Model scopes, eager loading, and search capabilities
- 📤 **Image Processing Pipeline**: Upload, process, and attach images with automatic optimization and transactional operations
- 🔄 **Picture Detachment System**: Complete workflow for detaching images and converting them back to available pool
- 🛠️ **Enhanced CI/CD Scripts**: Improved PowerShell scripts for testing and linting with flexible argument support

## 🎯 Quality Assurance

This project maintains high code quality standards through:

- **Test Coverage**: 924+ automated tests covering all API endpoints and functionality
- **Performance**: Fast test execution (~5.6 seconds) with parallel processing
- **Reliability**: 100% test pass rate with proper isolation (no external dependencies)
- **Code Standards**: Laravel Pint formatting and comprehensive linting
- **Security**: CodeQL scanning and dependency vulnerability checks
- **CI/CD**: Automated GitHub Actions workflow for continuous integration

## 🏗️ Project Architecture

This API is part of a broader modernization effort for Museum With No Frontiers. The new architecture consists of:

- **Management REST API** (this application): Provides secure endpoints for managing and updating the inventory database
- **Public Consultation REST API**: Grants controlled, read-only access to inventory data for public-facing applications
- **Client-side Web Applications**: Deployed separately, these applications interact with the consultation API to present data to end users

## 📚 Development Progress

Below you'll find automatically generated daily summaries documenting development activity on our main branch. Each summary includes:

- **Daily Overview**: Date, total commits, contributors, and file statistics
- **Related Issues/PRs**: Automatic extraction of GitHub issue and pull request references
- **Pull Request Merges**: Summary of merged PRs with links to original requests
- **Direct Commits**: Individual commits made directly to main branch
- **Contributor Activity**: Who contributed and how many commits they made
- **Impact Analysis**: Total files changed, lines added/removed across all commits

## 🔗 Related Projects

- [Inventory Management UI](https://github.com/metanull/inventory-management-ui) - A frontend application for this API

## Documentation Sections

### [API Documentation](api-documentation)

Interactive API documentation

### [Database models](models/)

Overview of the database models in the application, their main properties, and relationships.

### [Deployment Guide](deployment/)

Comprehensive deployment instructions for production and development environments.

### [Guidelines](guidelines/)

Comprehensive development guidelines covering API integration, coding standards, and testing practices.

### [Contributing](contributing)

Guidelines for contributing to the project, including development setup and workflow.

### [Development Archive](development-archive)

Historical development updates and project evolution.

### [Issues](https://github.com/metanull/inventory-app/issues)

Bug reports and feature requests

### [Source Code](https://github.com/metanull/inventory-app)

Complete source code and issue tracking

## 🚀 Technology Stack

### Core Stack

#### PHP

This API is developed using [PHP](https://www.php.net), a widely-used open source scripting language especially suited for web development. PHP powers millions of websites and applications worldwide, offering a mature ecosystem, strong community support, and excellent performance for building scalable server-side solutions. Its flexibility and extensive library support make it a reliable choice for modern web APIs.

#### Laravel

This API is built using [Laravel](https://laravel.com), a modern PHP web application framework known for its elegant syntax, robust features, and developer-friendly tools. Laravel is open source and released under the [MIT license](https://opensource.org/licenses/MIT), allowing free use, modification, and distribution.

#### MariaDB

The Museum With No Frontiers' production websites are powered by [MariaDB](https://mariadb.org), a popular open source relational database management system. MariaDB is renowned for its high performance, reliability, and compatibility with MySQL, making it a robust choice for mission-critical applications. Its active community and enterprise-grade features ensure ongoing support and scalability for MWNF's data infrastructure.

#### SQLite

For development and demonstration purposes, this application uses [SQLite](https://www.sqlite.org), a lightweight, file-based database engine. SQLite requires no server setup and is fully integrated into the Laravel ecosystem, making it ideal for local development, testing, and prototyping. Its simplicity and portability allow developers to quickly spin up and share working environments without complex configuration.

### Documentation

#### Scramble

This API uses [de:doc Scramble](https://scramble.dedoc.co/usage/getting-started) for automated OpenAPI documentation generation. Scramble automatically analyzes your Laravel routes, controllers, and models to generate accurate, interactive API documentation. It provides real-time documentation updates, automatic schema generation from Eloquent models, and comprehensive request/response examples, making API documentation maintenance effortless and always up-to-date.

#### Swagger UI

The interactive API documentation is powered by [Swagger UI](https://swagger.io/docs/open-source-tools/swagger-ui/usage/installation/#unpkg), an industry-standard tool for exploring and testing APIs. Swagger UI provides a user-friendly interface where developers can visualize API endpoints, examine request/response schemas, and execute live API calls directly from the browser, making API integration and testing straightforward and efficient.

#### OpenAPI Generator CLI

The project uses [OpenAPI Generator CLI](https://github.com/OpenAPITools/openapi-generator-cli) to automatically generate TypeScript-Axios client libraries from the OpenAPI specification. This tool ensures that client libraries are always in sync with the API, reducing manual maintenance and providing type-safe interfaces for consuming the API. The generator creates comprehensive documentation and examples, making API integration seamless for developers.

#### Ruby & Jekyll

The project documentation site is built using [Ruby 3](https://www.ruby-lang.org/) and [Jekyll](https://jekyllrb.com/), a static site generator that transforms markdown files into a beautiful, searchable documentation website. Jekyll's powerful templating system and GitHub Pages integration enable automatic documentation deployment, providing a professional documentation experience with minimal maintenance overhead.

### Code Quality

### PHPUnit

The project uses [PHPUnit](https://phpunit.de/), the industry-standard testing framework for PHP, to ensure the reliability and correctness of all application code. PHPUnit enables developers to write unit and feature tests that validate business logic, API endpoints, and integration scenarios. Test execution is automated in the CI/CD pipeline and enforced by pre-commit hooks, guaranteeing that all code changes are thoroughly tested before being merged. This rigorous approach to testing helps maintain a robust, bug-free codebase and supports rapid, confident development.

### Pint

The project uses [Pint](https://laravel.com/docs/12.x/pint), Laravel's official PHP code style fixer, to ensure consistent code formatting and adherence to modern PHP and Laravel standards. Pint automatically formats PHP code according to the PSR-12 standard and Laravel's own conventions, reducing code review overhead and eliminating style inconsistencies. It is integrated into the CI/CD pipeline and pre-commit hooks, so all PHP files are automatically checked and fixed before being committed, helping maintain a clean and professional codebase at all times.

### Prettier

The project uses [Prettier](https://prettier.io/) for automatic code formatting, ensuring consistent code style across JavaScript, TypeScript, CSS, and Markdown files. Prettier automatically formats code on save and during pre-commit hooks, eliminating style discussions and maintaining a uniform codebase appearance without manual intervention.

### Git Repository and CI/CD

### GitHub

This project is hosted on [GitHub](https://github.com/metanull/inventory-app), a leading platform for version control and collaborative software development. GitHub provides robust tools for code review, issue tracking, project management, and team collaboration, enabling efficient development workflows and maintaining high code quality standards through peer review and automated checks.

### GitHub Pages

The project documentation is deployed using [GitHub Pages](https://pages.github.com/), a static site hosting service that automatically builds and publishes documentation from the repository. GitHub Pages integration enables automatic documentation deployment, providing a professional documentation experience with minimal maintenance overhead and ensuring that documentation is always up-to-date with the latest changes.

### GitHub Actions

Continuous integration and deployment are powered by [GitHub Actions](https://github.com/features/actions), a powerful automation platform that runs workflows directly in the repository. GitHub Actions handles automated testing, code quality checks, security scanning, and deployment processes, ensuring that every change is validated and deployed safely without manual intervention.

### GitHub npm Package Repository

The TypeScript API client is published to [GitHub Packages](https://npm.pkg.github.com/), GitHub's integrated package registry that provides secure, private package hosting alongside the source code. This ensures that the API client packages are versioned, distributed, and managed in the same ecosystem as the source code, providing seamless integration and access control for development teams.

### AI assistant

#### GitHub Copilot and AI Agents

This project leverages [GitHub Copilot](https://github.com/features/copilot) as an AI coding assistant, powered by advanced models such as **GPT-4.1** and **Claude Sonnet 4**. Copilot and similar agents accelerate development by providing intelligent code suggestions, automated documentation, and context-aware refactoring. These tools help maintain high code quality, reduce manual effort, and ensure best practices are followed throughout the codebase.

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](https://github.com/metanull/inventory-app/blob/main/LICENSE) file for details.

---

_Last updated: {{ site.time | date: "%B %d, %Y" }}_
