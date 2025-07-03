---
layout: default
title: Home
nav_order: 1
---

[![CodeQL](https://github.com/metanull/inventory-app/actions/workflows/github-code-scanning/codeql/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/github-code-scanning/codeql) [![Composer+Phpunit+Pint](https://github.com/metanull/inventory-app/actions/workflows/laravel.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/laravel.yml) [![Dependabot](https://github.com/metanull/inventory-app/actions/workflows/dependabot/dependabot-updates/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/dependabot/dependabot-updates)[![GitHub Pages](https://github.com/metanull/inventory-app/actions/workflows/github-pages.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/github-pages.yml)

# Inventory Management API

{: .highlight }

> The **Inventory Management API** (inventory-app) is a RESTful API designed to manage the content of the Museum With No Frontiers' inventory database. This application serves as the management layer in a modern N-tier architecture, replacing legacy systems with a scalable, maintainable, and secure solution.

## ✨ Features

- 🚀 **RESTful API** for inventory management with complete CRUD operations
- 🔒 **User Authentication** and JWT token management
- 🛡️ **Database-agnostic** Eloquent models with UUID primary keys
- 📊 **Clean, maintainable** codebase following Laravel best practices
- 📚 **Automated API Documentation** with interactive testing capabilities
- ✅ **Comprehensive Test Suite**: 453 tests with 1163 assertions (100% reliable, ~5.6s execution)
- 🎨 **Image Processing Pipeline**: Automatic resizing, format optimization, and event-driven processing
- 🔧 **Advanced Query Features**: Model scopes, eager loading, and search capabilities

## 🎯 Quality Assurance

This project maintains high code quality standards through:

- **Test Coverage**: 453+ automated tests covering all API endpoints and functionality
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

## 📚 Documentation Sections

### [API Documentation](api-documentation)

Interactive API documentation

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

### About PHP

This API is developed using [PHP](https://www.php.net), a widely-used open source scripting language especially suited for web development. PHP powers millions of websites and applications worldwide, offering a mature ecosystem, strong community support, and excellent performance for building scalable server-side solutions. Its flexibility and extensive library support make it a reliable choice for modern web APIs.

### About Laravel

This API is built using [Laravel](https://laravel.com), a modern PHP web application framework known for its elegant syntax, robust features, and developer-friendly tools. Laravel is open source and released under the [MIT license](https://opensource.org/licenses/MIT), allowing free use, modification, and distribution.

### About MariaDB

The Museum With No Frontiers' production websites are powered by [MariaDB](https://mariadb.org), a popular open source relational database management system. MariaDB is renowned for its high performance, reliability, and compatibility with MySQL, making it a robust choice for mission-critical applications. Its active community and enterprise-grade features ensure ongoing support and scalability for MWNF's data infrastructure.

### About SQLite

For development and demonstration purposes, this application uses [SQLite](https://www.sqlite.org), a lightweight, file-based database engine. SQLite requires no server setup and is fully integrated into the Laravel ecosystem, making it ideal for local development, testing, and prototyping. Its simplicity and portability allow developers to quickly spin up and share working environments without complex configuration.

### About Scramble

This API uses [de:doc Scramble](https://scramble.dedoc.co/usage/getting-started) for automated OpenAPI documentation generation. Scramble automatically analyzes your Laravel routes, controllers, and models to generate accurate, interactive API documentation. It provides real-time documentation updates, automatic schema generation from Eloquent models, and comprehensive request/response examples, making API documentation maintenance effortless and always up-to-date.

### About Swagger UI

The interactive API documentation is powered by [Swagger UI](https://swagger.io/docs/open-source-tools/swagger-ui/usage/installation/#unpkg), an industry-standard tool for exploring and testing APIs. Swagger UI provides a user-friendly interface where developers can visualize API endpoints, examine request/response schemas, and execute live API calls directly from the browser, making API integration and testing straightforward and efficient.

### About Ruby & Jekyll

The project documentation site is built using [Ruby 3](https://www.ruby-lang.org/) and [Jekyll](https://jekyllrb.com/), a static site generator that transforms markdown files into a beautiful, searchable documentation website. Jekyll's powerful templating system and GitHub Pages integration enable automatic documentation deployment, providing a professional documentation experience with minimal maintenance overhead.

### About Husky

The project uses [Husky](https://typicode.github.io/husky) for managing Git hooks, enabling automated code quality checks before commits and pushes. Husky ensures that all committed code meets our quality standards by running linting, formatting, and tests automatically, preventing low-quality code from entering the repository and maintaining consistent code standards across all contributors.

### About lint-staged

This project integrates [lint-staged](https://www.npmjs.com/package/lint-staged) to run code quality tools only on staged files, improving performance and developer experience. lint-staged works with Husky to automatically format and lint only the files being committed, ensuring fast pre-commit checks without processing the entire codebase unnecessarily.

### About Prettier

The project uses [Prettier](https://prettier.io/) for automatic code formatting, ensuring consistent code style across JavaScript, TypeScript, CSS, and Markdown files. Prettier automatically formats code on save and during pre-commit hooks, eliminating style discussions and maintaining a uniform codebase appearance without manual intervention.

### About Stylelint

CSS and SCSS code quality is maintained using [Stylelint](https://stylelint.io/), a modern CSS linter that catches errors, enforces conventions, and suggests improvements. Stylelint integrates with our development workflow to ensure consistent CSS coding standards, proper syntax usage, and adherence to best practices across all stylesheets.

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](https://github.com/metanull/inventory-app/blob/main/LICENSE) file for details.

---

_Last updated: {{ site.time | date: "%B %d, %Y" }}_
