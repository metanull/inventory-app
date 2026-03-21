---
layout: default
title: Blade/Livewire Frontend
nav_order: 4
has_children: true
permalink: /frontend-blade/
---

# Web Interface

{: .important }

> This is the **main production interface** used by content managers and administrators to manage inventory data.

## What You Can Do

The web interface is the primary tool for day-to-day work with the inventory. Through it, users can:

- **Browse and search** items, partners, collections, projects, and all other entities
- **Create and edit** inventory records with full validation and multi-language support
- **Organise content** into collections, exhibitions, galleries, and thematic trails
- **Upload and manage images** — attach photos to items, collections, and partners
- **Manage users and permissions** — control who can view, create, edit, or delete records

Each entity type is colour-coded for visual clarity, making it easy to navigate between different areas of the system.

## How It Works

The interface is server-rendered: pages are built on the server and delivered as complete HTML. Interactive elements (inline editing, toggling, image management) use Livewire and Alpine.js to update the page without full reloads.

For development setup instructions, see the [Development Setup]({{ '/deployment/development-setup' | relative_url }}) guide.

## Developer Documentation

The sections below cover how the frontend is built — useful for developers extending or maintaining the interface.

- **[Components]({{ '/frontend-blade/components/' | relative_url }})** — Reusable Blade components (forms, tables, cards, buttons, etc.)
- **[Livewire]({{ '/frontend-blade/livewire/' | relative_url }})** — Reactive component patterns
- **[Alpine.js]({{ '/frontend-blade/alpine/' | relative_url }})** — Client-side interaction patterns
- **[Styling]({{ '/frontend-blade/styling/' | relative_url }})** — Tailwind CSS conventions and entity colour system
- **[Views]({{ '/frontend-blade/views' | relative_url }})** — Page structure and entity views
- **[Routing]({{ '/frontend-blade/routing' | relative_url }})** — Web route conventions
- **[Guidelines]({{ '/frontend-blade/guidelines' | relative_url }})** — Frontend development standards
- **[Testing]({{ '/frontend-blade/testing' | relative_url }})** — How to test frontend components

## Related Documentation

- [Core Concepts]({{ '/concepts' | relative_url }}) — Understand the business model
- [Database Models]({{ '/models/' | relative_url }}) — Data structure reference
- [API Documentation]({{ '/api/' | relative_url }}) — REST API for programmatic access

---

{: .note }

> For the Vue.js sample application (a reference for external API consumers), see [Vue.js Sample App]({{ '/frontend-vue-sample/' | relative_url }}).
