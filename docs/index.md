---
layout: default
title: Home
nav_order: 1
---

[![github](https://img.shields.io/badge/Source-github-151013.svg?logo=github&logoColor=white&labelColor=555555)](https://github.com/metanull/inventory-app)
[![LICENSE](https://img.shields.io/badge/license-MIT-428f7e.svg?logo=open%20source%20initiative&logoColor=white&labelColor=555555)](https://github.com/metanull/inventory-app/blob/main/LICENSE)
[![Continuous Integration](https://github.com/metanull/inventory-app/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/continuous-integration.yml)
[![Build](https://github.com/metanull/inventory-app/actions/workflows/build.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/build.yml)
[![Deploy](https://github.com/metanull/inventory-app/actions/workflows/deploy.yml/badge.svg)](https://github.com/metanull/inventory-app/actions/workflows/deploy.yml)
[![Documentation](https://github.com/metanull/inventory-app/actions/workflows/continuous-deployment_github-pages.yml/badge.svg)](https://metanull.github.io/inventory-app)

# Inventory Management System

The Inventory Management System is the digital backbone of **Museum With No Frontiers (MWNF)**. It stores and serves the inventory of museum artifacts, monuments, and related cultural heritage content managed by partner institutions around the world.

The system provides:

- A **web interface** for content managers and administrators to browse, create, and edit inventory records.
- A **REST API** for programmatic access, enabling external applications to read and manage inventory data.
- **Multi-language, multi-audience content** — the same artifact can be described in different languages and for different audiences (general public, academic, educational, etc.).
- **Image management** — upload, process, and attach images to items, collections, and partners.
- **Role-based access control** — fine-grained permissions for different user roles.

## Getting Started

### For business users and content managers

Start with the [Core Concepts]({{ '/concepts' | relative_url }}) page — it explains every entity and process in plain language.

### For developers and collaborators

1. [Core Concepts]({{ '/concepts' | relative_url }}) — Understand the domain model before touching any code
2. [Development Setup]({{ '/deployment/development-setup' | relative_url }}) — Set up your local environment
3. [Contributing]({{ '/development/contributing' | relative_url }}) — Workflow, quality standards, and PR process

## Documentation Sections

### Understanding the System

| Section | Audience | Description |
| ------- | -------- | ----------- |
| [Core Concepts]({{ '/concepts' | relative_url }}) | Everyone | What the system does, how entities relate, and key business rules |
| [Database Models]({{ '/models/' | relative_url }}) | Developers | Auto-generated reference for all data models, fields, and relationships |

### Using the System

| Section | Audience | Description |
| ------- | -------- | ----------- |
| [Web Interface]({{ '/frontend-blade/' | relative_url }}) | Content managers, Developers | The main production UI for managing inventory content |
| [API Documentation]({{ '/api/' | relative_url }}) | Developers | Interactive API explorer (Swagger UI) and TypeScript client |

### Running and Deploying

| Section | Audience | Description |
| ------- | -------- | ----------- |
| [Deployment Guide]({{ '/deployment/' | relative_url }}) | Developers, Ops | Development setup, production deployment, and server configuration |

### Contributing

| Section | Audience | Description |
| ------- | -------- | ----------- |
| [Development]({{ '/development/' | relative_url }}) | Developers | Contributing guidelines, testing, CI/CD workflows, and project history |
| [Backend Guidelines]({{ '/guidelines/' | relative_url }}) | Developers | Project-specific coding conventions and patterns |

### Reference Applications

| Section | Audience | Description |
| ------- | -------- | ----------- |
| [Vue.js Sample App]({{ '/frontend-vue-sample/' | relative_url }}) | Developers | A demo SPA showing how to consume the API with the TypeScript client |

{: .note }

> The Vue.js sample app is a **reference implementation** for external developers. The main user interface is the [Web Interface (Blade/Livewire)]({{ '/frontend-blade/' | relative_url }}).

---

## Additional Resources

- [GitHub Repository](https://github.com/metanull/inventory-app) — Source code and issue tracking
- [GitHub Issues](https://github.com/metanull/inventory-app/issues) — Bug reports and feature requests
- [Development Archive]({{ '/development/archive' | relative_url }}) — Commit history

{: .note }

> This documentation website is built with Jekyll and deployed automatically to GitHub Pages. For details on how it works, see [Documentation Site]({{ '/development/documentation-site/' | relative_url }}).

## License

This project is licensed under the MIT License — see the [LICENSE](https://github.com/metanull/inventory-app/blob/main/LICENSE) file for details.

---

_Last updated: {{ site.time | date: "%B %d, %Y" }}_
