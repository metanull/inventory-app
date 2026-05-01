---
layout: default
title: Vue.js Sample Frontend
nav_order: 80
has_children: true
permalink: /frontend-vue-sample/
---

# Vue.js Sample Frontend

{: .label .label-blue }
Sample Application

{: .note }

> This is a reference implementation for external developers who want to build client applications on top of the management API. It is not the main back-office. The main back-office is Filament `/admin`.

This Vue 3 single-page application demonstrates how to consume the management API using the published TypeScript client package (`@metanull/inventory-app-api-client`). It is not intended for production use by end users.

## Quick Start

All commands are run from the `/spa` directory:

```bash
npm install          # Install dependencies
npm run dev          # Start development server
npm run build        # Build for production
npm run test         # Run tests
npm run quality-check # Full quality check
```

## Architecture

- **Views** - Page-level components (list pages, detail pages)
- **Components** - Reusable UI elements (cards, forms, tables, display)
- **Stores** - Pinia stores managing data and API calls
- **Router** - Vue Router configuration

## Integration with Laravel

Laravel serves the initial HTML shell at the `/cli` route. From there, Vue Router takes over client-side navigation. API calls are authenticated through Laravel Sanctum.

## Documentation Sections

- [Quick Reference]({{ '/frontend-vue-sample/quick-reference' | relative_url }}) - Developer cheat sheet
- [Application Architecture]({{ '/frontend-vue-sample/application-architecture' | relative_url }}) - Structure and design patterns
- [Page Patterns]({{ '/frontend-vue-sample/page-patterns' | relative_url }}) - Standardized page implementations
- [Frontend Guidelines]({{ '/frontend-vue-sample/guidelines/' | relative_url }}) - Coding standards and testing
- [Component Reference]({{ '/frontend-vue-sample/components/' | relative_url }}) - All Vue.js components
