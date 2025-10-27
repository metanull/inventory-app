---
layout: default
title: Blade/Livewire Frontend
nav_order: 4
has_children: true
permalink: /frontend-blade/
---

# Blade/Livewire Frontend Documentation

{: .important }
> This is the **MAIN** frontend that end users interact with. For the sample Vue.js API client demo, see [Vue.js Sample App]({{ '/frontend-vue-sample/' | relative_url }}).

## Overview

The Blade/Livewire frontend is a server-rendered web application built with:

- **Laravel Blade** - Templating engine for views
- **Livewire 3** - Reactive components without JavaScript frameworks
- **Alpine.js** - Lightweight JavaScript for UI interactions
- **Tailwind CSS** - Utility-first CSS framework
- **Heroicons** - Icon set

## Technology Stack

| Technology | Version | Purpose |
|-----------|---------|---------|
| Laravel Blade | 11+ | Server-side templating |
| Livewire | 3.6+ | Reactive components |
| Alpine.js | 3.x | Client-side interactions |
| Tailwind CSS | 3.x | Styling framework |
| Heroicons | 2.x | SVG icons |

## Architecture

The frontend follows a component-based architecture:

```
resources/views/
├── layouts/           # Base layouts (app.blade.php, guest.blade.php)
├── components/        # Reusable Blade components
├── livewire/          # Livewire components
├── [entity]/          # Entity-specific views (items, partners, etc.)
│   ├── index.blade.php
│   ├── show.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── _form.blade.php
└── ...
```

## Key Features

### Server-Side Rendering
- Fast initial page loads
- SEO-friendly
- Progressive enhancement

### Reactive Components
- Real-time updates via Livewire
- No full page refreshes
- Minimal JavaScript required

### Responsive Design
- Mobile-first approach
- Tailwind utility classes
- Consistent UI across devices

### Entity Color System
- Color-coded entities for visual clarity
- Consistent color usage throughout the app
- Configurable via entity color service

## Getting Started

### Development Setup

1. **Start the development server:**
   ```bash
   php artisan serve
   ```

2. **Watch for asset changes:**
   ```bash
   npm run dev
   ```

3. **Access the application:**
   - Web frontend: `http://localhost:8000/web`
   - API docs: `http://localhost:8000/api/docs`

### Common Tasks

- **Create a new view:** See [Components](components/)
- **Add Livewire component:** See [Livewire](livewire/)
- **Style with Tailwind:** See [Styling](styling/)
- **Add Alpine.js interactions:** See [Alpine.js](alpine/)

## Directory Structure

- **[Components](components/)** - Blade component library
- **[Livewire](livewire/)** - Livewire component patterns
- **[Alpine.js](alpine/)** - JavaScript interaction patterns
- **[Styling](styling/)** - Tailwind conventions and entity colors

## Related Documentation

- [API Documentation]({{ '/api/' | relative_url }})
- [Database Models]({{ '/models/' | relative_url }})
- [Backend Guidelines]({{ '/guidelines/' | relative_url }})
- [Testing]({{ '/development/testing' | relative_url }})

---

{: .fs-6 .fw-300 }
**Note:** This documentation covers the server-rendered Blade/Livewire frontend. For the Vue.js sample application, see [Vue.js Sample App]({{ '/frontend-vue-sample/' | relative_url }}).
