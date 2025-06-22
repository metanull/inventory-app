# Inventory App

Welcome to the Inventory App project! This application provides a robust and secure REST API for managing your inventory database, built with Laravel 12+. It features user management, JWT authentication, and comprehensive API documentation powered by [dedoc/Scramble](https://github.com/dedoc/scramble).

## Features

- RESTful API for inventory management
- User and JWT token management
- Database-agnostic Eloquent models
- Clean, maintainable, and secure codebase
- Automated API documentation

For detailed setup instructions, usage examples, and contribution guidelines, please see the [readme](./readme).

# Inventory Management API

[![CodeQL](https://github.com/metanull/inventory-app/actions/workflows/github-code-scanning/codeql/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/github-code-scanning/codeql) [![Laravel](https://github.com/metanull/inventory-app/actions/workflows/laravel.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/laravel.yml) [![Dependabot Updates](https://github.com/metanull/inventory-app/actions/workflows/dependabot/dependabot-updates/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/dependabot/dependabot-updates)


The **Inventory Management API** (inventory-app) is a RESTful API designed to manage the content of the Museum With No Frontiers' inventory database. This application serves as the management layer in a modern N-tier architecture, replacing legacy systems with a scalable, maintainable, and secure solution.

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

## Author

This work is authored and maintained by Pascal Havelange.
## References

- [Museum With No Frontiers Portal](https://museumwnf.org): Access all MWNF products and resources.
- [MWNF Credits](https://www.museumwnf.org/about/credits): Information about the stakeholders and contributors to the MWNF project.
- [Project Repository on GitHub](https://github.com/metanull/inventory-app): Public source code and issue tracking for this API.
- [MWNF Jira (Atlassian)](https://mwnf.atlassian.net/jira/software/c/projects/MWNF/boards/2): Project management and issue tracking.
- [MWNF Confluence (Atlassian)](https://mwnf.atlassian.net/wiki): Project documentation and collaboration.
- [MWNF Bitbucket (Atlassian)](https://bitbucket.org/mwnf): Additional code repositories and version control.
- [Laravel Homepage](https://laravel.com): Official website for the Laravel framework.

## Technology Stack

### About PHP

This API is developed using [PHP](https://www.php.net), a widely-used open source scripting language especially suited for web development. PHP powers millions of websites and applications worldwide, offering a mature ecosystem, strong community support, and excellent performance for building scalable server-side solutions. Its flexibility and extensive library support make it a reliable choice for modern web APIs.

### About Laravel

This API is built using [Laravel](https://laravel.com), a modern PHP web application framework known for its elegant syntax, robust features, and developer-friendly tools. Laravel is open source and released under the [MIT license](https://opensource.org/licenses/MIT), allowing free use, modification, and distribution.

### About MariaDB

The Museum With No Frontiers' production websites are powered by [MariaDB](https://mariadb.org), a popular open source relational database management system. MariaDB is renowned for its high performance, reliability, and compatibility with MySQL, making it a robust choice for mission-critical applications. Its active community and enterprise-grade features ensure ongoing support and scalability for MWNF's data infrastructure.

### About SQLite

For development and demonstration purposes, this application uses [SQLite](https://www.sqlite.org), a lightweight, file-based database engine. SQLite requires no server setup and is fully integrated into the Laravel ecosystem, making it ideal for local development, testing, and prototyping. Its simplicity and portability allow developers to quickly spin up and share working environments without complex configuration.
