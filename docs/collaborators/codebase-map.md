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
| `.legacy-code/` | Legacy application sources kept for behavioural reference. |
| `.new-architecture/` | The website platform repositories, checked out for reference. |
| `docs/` | Jekyll documentation site. |
| `api-client/` | Generated TypeScript management API client. Do not edit by hand. |
| `spa/` | Vue sample application for API client integration. |
| `tests/Filament/` | Filament `/admin` tests. |
| `tests/Api/` | Management API tests. |
| `tests/Unit/` | Model, request, service, and helper unit tests. |

## Local-only submodules

The last three paths above are Git submodules declared in `.gitmodules` but
ignored by `.gitignore`, so this repository records no commit for them. Clone
them when you need them:

```bash
git submodule update --init .new-architecture
```

`.legacy-database/` and `.legacy-code/` are private Bitbucket repositories, and
legacy code must never be copied into a public repository. `.new-architecture/`
holds the public [website platform](https://github.com/metanull/website-template)
repositories — `viewer-core`, `viewer-layout`, `viewer-workflows`,
`website-template` and one repository per website — which are useful to read
while working on the exporters and viewers under `scripts/`. They are ignored
rather than pinned because each is released on its own cadence: a recorded
pointer would be stale within the day and would turn every website commit into
a change to this repository.

## Main boundaries

- Use Filament resources and pages for new back-office features.
- Keep `/admin` authentication isolated from `/web` authentication.
- Keep importer transformation logic in `scripts/importer/src/domain/transformers/` when the logic maps source data to Inventory data.
- Keep importer persistence in the write strategy instead of scattering SQL across helpers.
- Keep generated documentation and generated clients out of manual edits.

## Business references

- [Core Model](../understanding/core-model) explains the meaning of the main entities.
- [Legacy Import](../understanding/legacy-import) explains source-to-target import behavior.
