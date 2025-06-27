---
layout: home
title: "Inventory Management API - Development Blog"
---

# Inventory Management API - Development Blog

[![CodeQL](https://github.com/metanull/inventory-app/actions/workflows/github-code-scanning/codeql/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/github-code-scanning/codeql) [![Composer+Phpunit+Pint](https://github.com/metanull/inventory-app/actions/workflows/laravel.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/laravel.yml) [![Dependabot](https://github.com/metanull/inventory-app/actions/workflows/dependabot/dependabot-updates/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/dependabot/dependabot-updates)[![GitHub Pages](https://github.com/metanull/inventory-app/actions/workflows/github-pages.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/github-pages.yml)


Welcome to the development blog for the **Inventory Management API**! This blog automatically tracks our development progress by generating posts from every push to the main branch, grouping related commits together by their original pull requests or direct pushes.

## About the Project

The **Inventory Management API** (inventory-app) is a RESTful API designed to manage the content of the Museum With No Frontiers' inventory database. This application serves as the management layer in a modern N-tier architecture, replacing legacy systems with a scalable, maintainable, and secure solution.

## Features

- **RESTful API** for inventory management with complete CRUD operations
- **User Authentication** and JWT token management  
- **Database-agnostic** Eloquent models with UUID primary keys
- **Clean, maintainable** codebase following Laravel best practices
- **Automated API Documentation** with interactive testing capabilities
- **Comprehensive Test Suite**: 453 tests with 1163 assertions (100% reliable, ~5.6s execution)
- **Image Processing Pipeline**: Automatic resizing, format optimization, and event-driven processing
- **Advanced Query Features**: Model scopes, eager loading, and search capabilities

## Quality Assurance

This project maintains high code quality standards through:

- **Test Coverage**: 453+ automated tests covering all API endpoints and functionality
- **Performance**: Fast test execution (~5.6 seconds) with parallel processing  
- **Reliability**: 100% test pass rate with proper isolation (no external dependencies)
- **Code Standards**: Laravel Pint formatting and comprehensive linting
- **Security**: CodeQL scanning and dependency vulnerability checks
- **CI/CD**: Automated GitHub Actions workflow for continuous integration

## Project Architecture

This API is part of a broader modernization effort for Museum With No Frontiers. The new architecture consists of:

- **Management REST API** (this application): Provides secure endpoints for managing and updating the inventory database
- **Public Consultation REST API**: Grants controlled, read-only access to inventory data for public-facing applications  
- **Client-side Web Applications**: Deployed separately, these applications interact with the consultation API to present data to end users

## Development Progress

Below you'll find automatically generated blog posts documenting every push to our main branch. Each post groups related commits and includes:

- **Push Details**: Author, date, SHA, and commit count
- **Pull Request Info**: PR number and title (for merged PRs)
- **Commit Breakdown**: Individual commits with their changes
- **File Statistics**: Total files changed, insertions, and deletions
- **Impact Analysis**: How the changes affect the project

## Links

- **[Source Code](https://github.com/metanull/inventory-app)**: Complete source code and issue tracking
- **[API Documentation](https://github.com/metanull/inventory-app#api-documentation)**: Interactive API documentation  
- **[Contributing](https://github.com/metanull/inventory-app#contributing)**: Guidelines for contributing to the project
- **[Issues](https://github.com/metanull/inventory-app/issues)**: Bug reports and feature requests

---

*This blog is automatically updated with each push to the main branch. Posts are generated using GitHub Actions and Jekyll, grouping commits by their original pull requests.*

## Technology Stack

### About PHP

This API is developed using [PHP](https://www.php.net), a widely-used open source scripting language especially suited for web development. PHP powers millions of websites and applications worldwide, offering a mature ecosystem, strong community support, and excellent performance for building scalable server-side solutions. Its flexibility and extensive library support make it a reliable choice for modern web APIs.

### About Laravel

This API is built using [Laravel](https://laravel.com), a modern PHP web application framework known for its elegant syntax, robust features, and developer-friendly tools. Laravel is open source and released under the [MIT license](https://opensource.org/licenses/MIT), allowing free use, modification, and distribution.

### About MariaDB

The Museum With No Frontiers' production websites are powered by [MariaDB](https://mariadb.org), a popular open source relational database management system. MariaDB is renowned for its high performance, reliability, and compatibility with MySQL, making it a robust choice for mission-critical applications. Its active community and enterprise-grade features ensure ongoing support and scalability for MWNF's data infrastructure.

### About SQLite

For development and demonstration purposes, this application uses [SQLite](https://www.sqlite.org), a lightweight, file-based database engine. SQLite requires no server setup and is fully integrated into the Laravel ecosystem, making it ideal for local development, testing, and prototyping. Its simplicity and portability allow developers to quickly spin up and share working environments without complex configuration.
