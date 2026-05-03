---
layout: default
title: Filament Back-Office
parent: Collaborator Guide
nav_order: 2
---

# Filament Back-Office

Filament `/admin` is the main integrated back-office. Treat it as the primary user interface for content managers and administrators.

## Where to work

| Area | Path |
|---|---|
| Filament resources and pages | `app/Filament/` |
| Filament panel provider | `app/Providers/Filament/` |
| Filament auth pages | `app/Filament/Auth/` |
| Filament tests | `tests/Filament/` |
| Policies | `app/Policies/` |

## Authorization model

Filament uses three authorization tiers:

1. Panel access uses the `access-admin-panel` permission.
2. Navigation and resource visibility use feature permissions such as `view-data`, `manage-users`, `manage-roles`, `manage-settings`, and `manage-reference-data`.
3. Record and action authorization uses existing policies in `app/Policies/`.

## Auth isolation

Keep `/admin` and `/web` authentication flows isolated. Filament login, MFA challenge, and MFA setup stay in Filament pages. Do not route `/admin` through Fortify web routes, Blade auth views, or shared session markers from `/web`.

## Test placement

Put new Filament tests under `tests/Filament/`. Do not add new back-office tests under `tests/Web/`.

## Business references

- [Inventory Principles](../understanding/inventory-principles) explains why Filament is the main back-office.
- [Core Model](../understanding/core-model) explains the entities shown in Filament resources.
