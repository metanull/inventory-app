---
layout: default
title: Database Models
nav_order: 6
has_children: true
permalink: /models/
---

# Database Models

{: .note }

> **Auto-Generated Documentation:** Model documentation is automatically generated from Laravel models using `php artisan docs:model`.

This section contains complete documentation for all database models in the application.

## Navigation

The model documentation is organized by category. Use the navigation sidebar or visit the sections below:

- [All Models (Alphabetical)]({{ site.baseurl }}/\_model/)

## What's Included

The auto-generated documentation includes:

- **Database Schemas** - Table structures with column types and constraints
- **Relationships** - BelongsTo, HasMany, BelongsToMany, and polymorphic relationships
- **Fillable Fields** - Mass-assignable attributes
- **Attribute Casting** - Type casting for model attributes
- **Query Scopes** - Custom query methods
- **Pivot Tables** - Many-to-many relationship details

## Regenerating Documentation

To regenerate the model documentation after schema changes:

```bash
php artisan docs:model --force
```

This updates all model documentation files in the `_model/` directory.

---

{: .fs-6 .fw-300 }
For complete model details, see the [Generated Model Documentation]({{ site.baseurl }}/\_model/).
