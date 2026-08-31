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

## Submodules

`.legacy-database/` and `.legacy-code/` are declared in `.gitmodules` but
ignored by `.gitignore`, so this repository records no commit for them. They
mirror **private** Bitbucket repositories: pinning them would make
`git submodule update --init --recursive` fail for every contributor without
Bitbucket access, and legacy code must never be copied into a public
repository. Clone them when you need them:

```bash
git submodule update --init .legacy-database .legacy-code
```

`.new-architecture/` holds the public [website platform](https://github.com/metanull/website-template)
repositories — `viewer-core`, `viewer-layout`, `viewer-workflows`,
`website-template` and one repository per website — which are useful to read
while working on the exporters and viewers under `scripts/`. Those are public,
so they are pinned like ordinary submodules:

```bash
git submodule update --init .new-architecture
```

Each is released on its own cadence, so these pointers go stale quickly and
`git status` will routinely report *new commits* under `.new-architecture/`.
That noise is the point. While the directory was ignored, the checkouts drifted
with nothing to say so, and reading a website's `package.json` from here
answered with whatever commit was last checked out rather than with `main` —
silently, and wrongly. Refresh them with

```bash
git submodule update --remote .new-architecture
```

Commit the resulting pointer bump only when you mean to record it. Nothing in
this repository builds, tests or deploys against these submodules — CI does not
even fetch them — so a stale pointer breaks nothing here. It only ever misleads
a reader, which is why the drift is now visible instead of hidden. When you need
a website's *current* state rather than a local snapshot, read its `main`
branch on GitHub.

## Main boundaries

- Use Filament resources and pages for new back-office features.
- Keep `/admin` authentication isolated from `/web` authentication.
- Keep importer transformation logic in `scripts/importer/src/domain/transformers/` when the logic maps source data to Inventory data.
- Keep importer persistence in the write strategy instead of scattering SQL across helpers.
- Keep generated documentation and generated clients out of manual edits.

## Business references

- [Core Model](../understanding/core-model) explains the meaning of the main entities.
- [Legacy Import](../understanding/legacy-import) explains source-to-target import behavior.
