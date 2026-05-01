---
layout: default
title: Codebase Map
parent: Collaborator Guide
nav_order: 1
---

# Codebase Map

Use this map to find the part of the repository that owns a task.

| Path | Purpose |
|---|---|
| `app/Models/` | Laravel models for Inventory entities. |
| `app/Filament/` | Filament `/admin` resources, pages, widgets, and auth pages. |
| `app/Policies/` | Record-level authorization policies. |
| `app/Http/Controllers/` | Management API and remaining web controllers. |
| `app/Http/Resources/` | API response resource classes. |
| `app/Http/Requests/` | Validation request classes. |
| `database/migrations/` | Schema changes. Create new migrations instead of editing existing migrations. |
| `database/factories/` | Test data factories. |
| `database/seeders/` | Reference and development seed data. |
| `scripts/importer/` | Node.js legacy data importer. |
| `.legacy-database/` | Legacy DDL and data exports used for migration analysis. |
| `docs/` | Jekyll documentation site. |
| `api-client/` | Generated TypeScript management API client. Do not edit by hand. |
| `spa/` | Vue sample application for API client integration. |
| `tests/Filament/` | Filament `/admin` tests. |
| `tests/Api/` | Management API tests. |
| `tests/Unit/` | Model, request, service, and helper unit tests. |

## Main boundaries

- Use Filament resources and pages for new back-office features.
- Keep `/admin` authentication isolated from `/web` authentication.
- Keep importer transformation logic in `scripts/importer/src/domain/transformers/` when the logic maps source data to Inventory data.
- Keep importer persistence in the write strategy instead of scattering SQL across helpers.
- Keep generated documentation and generated clients out of manual edits.

## Business references

- [Core Model](../understanding/core-model) explains the meaning of the main entities.
- [Legacy Import](../understanding/legacy-import) explains source-to-target import behavior.
