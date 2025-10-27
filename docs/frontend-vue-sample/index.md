---
layout: default
title: Vue.js Sample Frontend
nav_order: 7
has_children: true
permalink: /frontend-vue-sample/
---

# Vue.js Sample Frontend

{: .label .label-blue }
Sample Application

{: .note }

> **Note:** This is a SAMPLE single-page application demonstrating API usage.
> It is NOT the main frontend for end users, see [Blade/Livewire Frontend]({{ '/frontend-blade/' | relative_url }}).

This section covers the Vue.js sample application that demonstrates how to interact with the Laravel API.

## Overview

The frontend is built with:

- **Vue.js 3** with Composition API and TypeScript
- **Tailwind CSS** for styling
- **Pinia** for state management
- **Vue Router** for routing
- **Vite** for build tooling

## Quick Start

```bash
# Install dependencies
npm install

# Development server
npm run dev

# Build for production
npm run build

# Run tests
npm run test

# Quality checks
npm run quality-check
```

## Architecture

The frontend follows the component-based architecture with clear separation of concerns:

- **Views**: Page-level components
- **Components**: Reusable UI components
- **Stores**: Pinia stores for state management
- **Router**: Vue Router configuration
- **Utils**: Utility functions and helpers

## Integration with Laravel

The Vue.js application is integrated into Laravel as a Single Page Application (SPA):

- Laravel serves the initial HTML template
- Vue.js takes over client-side routing
- API communication through Laravel Sanctum
- Built assets served by Laravel Vite plugin

## Documentation Sections

- [Quick Reference]({{ '/frontend-vue-sample/quick-reference' | relative_url }}) - Developer cheat sheet and quick start guide
- [Application Architecture]({{ '/frontend-vue-sample/application-architecture' | relative_url }}) - Overall structure and design patterns
- [Page Patterns]({{ '/frontend-vue-sample/page-patterns' | relative_url }}) - for standardized page implementations
- [Frontend Guidelines]({{ '/frontend-vue-sample/guidelines/' | relative_url }}) - Coding standards, testing, and best practices
- [Component Reference]({{ '/frontend-vue-sample/components/' | relative_url }}) - Documentation for all Vue.js components
