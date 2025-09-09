---
layout: default
title: Frontend Documentation
nav_order: 4
has_children: true
---

# Frontend Documentation

This section covers the Vue.js frontend application integrated into the Laravel inventory management system.

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

- [Quick Reference](quick-reference.md) - Developer cheat sheet and quick start guide
- [Application Architecture](application-architecture.md) - Overall structure and design patterns
- [Page Patterns](page-patterns.md) - **START HERE** for standardized page implementations  
- [Frontend Guidelines](guidelines/) - Coding standards, testing, and best practices
- [Component Reference](components/) - Documentation for all Vue.js components
