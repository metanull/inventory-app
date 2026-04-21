---
layout: default
title: Request-Driven List Extensions
parent: Development
nav_order: 6
description: Audit of upcoming request-driven list migrations and the minimal shared infrastructure gaps closed by Epic 8.
---

# Request-Driven List Extensions

This note audits every remaining web list-like page that is scheduled for the request-driven rollout in Epics 9 to 12. It records which shared primitives already fit, which pages need additional support, and which gaps are intentionally deferred until the actual page migration.

## Shared Primitive Baseline

- `ListDefinition` already covers query parameter declaration, filter validation, normalization, eager-load declarations, and sort whitelists.
- `IndexListRequest` already normalizes and validates GET state into a `ListState` object.
- `ListState` already preserves filters, search, sort, direction, page, and per-page in query-string form.
- Shared Blade list primitives already cover search forms, sort links, and pagination for flat list pages.

Epic 8 closes only the gaps that repeat across future cohorts:

- Declarative multi-column search, including ISO-code search.
- Declarative required scope filters for parent-scoped lists.
- Declarative sort-column helpers for future related-column sorts.
- Render-only parent-context header and reusable ISO-code column rendering.

## Page Audit

| Page                            | Filters                                                                  | Sortable columns                                                               | Parent scope        | Primitives reused                                                   | Gap                                                                                                                                                |
| ------------------------------- | ------------------------------------------------------------------------ | ------------------------------------------------------------------------------ | ------------------- | ------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------- |
| Tag                             | `q` only                                                                 | `internal_name`, `description`, timestamps                                     | None                | `IndexListRequest`, `ListState`, search form, sort link, pagination | None. Existing primitives already fit.                                                                                                             |
| Author                          | `q` only                                                                 | `internal_name`, timestamps                                                    | None                | Same flat-list baseline as Partner                                  | None. Existing primitives already fit.                                                                                                             |
| Project                         | `q` only                                                                 | `internal_name`, timestamps                                                    | None                | Same flat-list baseline as Partner                                  | None. Existing primitives already fit.                                                                                                             |
| Context                         | `q`, optional `is_default` in later follow-up if desired                 | `internal_name`, timestamps                                                    | None                | Same flat-list baseline as Partner                                  | No Epic 8 gap. Any `is_default` filter is page-specific and can be declared directly in the future list definition.                                |
| Country                         | `q` across `id` and `internal_name`                                      | `id`, `internal_name`, timestamps                                              | None                | Flat-list baseline                                                  | Needs declarative multi-column search and reusable code-column rendering.                                                                          |
| Language                        | `q` across `id` and `internal_name`, optional default-state filter later | `id`, `internal_name`, timestamps                                              | None                | Flat-list baseline                                                  | Needs declarative multi-column search and reusable code-column rendering.                                                                          |
| Glossary                        | `q` across `internal_name` and `description`                             | `internal_name`, `description`, timestamps                                     | None                | Flat-list baseline                                                  | None. Existing primitives already fit.                                                                                                             |
| User admin                      | Admin request object, search, role/status filters                        | Name, email, timestamps                                                        | None                | `ListState`, sort links, pagination                                 | Deferred. Admin screens already use custom requests and are outside Epics 9 to 12. No Epic 8 infrastructure change required.                       |
| Role admin                      | Admin request object, permission filters                                 | Name, timestamps                                                               | None                | `ListState`, sort links, pagination                                 | Deferred. Same rationale as user admin.                                                                                                            |
| ItemTranslation                 | `q`, `language_id`, `context_id`, mandatory `item_id`                    | `name`, `updated_at`, future `language.internal_name`, `context.internal_name` | Item                | `IndexListRequest`, `ListState`, shared search and pagination       | Needs required scope-filter support, future related-column sort helpers, and parent-context header.                                                |
| PartnerTranslation              | `q`, `language_id`, `context_id`, mandatory `partner_id`                 | `name`, `updated_at`, future `language.internal_name`, `context.internal_name` | Partner             | Same as ItemTranslation                                             | Needs required scope-filter support, future related-column sort helpers, and parent-context header.                                                |
| CollectionTranslation           | `q`, `language_id`, `context_id`, mandatory `collection_id`              | `name`, `updated_at`, future `language.internal_name`, `context.internal_name` | Collection          | Same as ItemTranslation                                             | Needs required scope-filter support, future related-column sort helpers, and parent-context header.                                                |
| ItemItemLink                    | `q`, mandatory `item_id`                                                 | `created_at`, `updated_at`, optional target label                              | Item                | `IndexListRequest`, `ListState`, pagination                         | Needs required scope-filter support and parent-context header. Related target sorting is deferred until the page migration if it proves necessary. |
| AvailableImage                  | `q` on comment or filename, optional future image-state filters          | `created_at`, `updated_at`                                                     | None                | Flat-list baseline                                                  | No Epic 8 gap. Search columns can be declared directly by the future list definition.                                                              |
| GlossarySpelling                | Minimal or no filters, mandatory `glossary_id`                           | Language or created timestamp if later added                                   | Glossary            | `ListState`, pagination, future parent header                       | Needs parent-context header and required scope-filter support if migrated into a request-driven index.                                             |
| GlossaryTranslation             | Minimal or no filters, mandatory `glossary_id`                           | Language or created timestamp if later added                                   | Glossary            | `ListState`, pagination, future parent header                       | Needs parent-context header and required scope-filter support if migrated into a request-driven index.                                             |
| ItemImage gallery               | Minimal filters, mandatory `item_id`                                     | Display order, created timestamp                                               | Item                | `ListState`, pagination, future parent header                       | Needs parent-context header. Gallery-specific drag/drop or media presentation is deferred to Epic 12.                                              |
| PartnerImage gallery            | Minimal filters, mandatory `partner_id`                                  | Display order, created timestamp                                               | Partner             | Same as ItemImage gallery                                           | Needs parent-context header.                                                                                                                       |
| PartnerTranslationImage gallery | Minimal filters, mandatory `partner_translation_id`                      | Display order, created timestamp                                               | Partner translation | Same as ItemImage gallery                                           | Needs parent-context header.                                                                                                                       |
| CollectionImage gallery         | Minimal filters, mandatory `collection_id`                               | Display order, created timestamp                                               | Collection          | Same as ItemImage gallery                                           | Needs parent-context header.                                                                                                                       |

## Gap Summary

### Covered by Story 8.2

- Multi-column search for code-plus-name pages such as Country and Language.
- Required scope filters declared from the list definition instead of bespoke request subclasses.
- Sort-column lookup through `ListSortDefinition`, so future pages can map sort keys to joined columns like `languages.internal_name`.

### Covered by Story 8.3

- Parent-context header rendered from controller-provided data only.
- Reusable code-column cell rendering for ISO-code pages.
- Layout support for inserting parent context above the list filter bar without changing existing Milestone 1 pages.

### Explicitly Deferred

- Admin user and role screens stay on their existing custom request flow for now. They do not block Epics 9 to 12.
- Page-specific query shaping, such as translation-name relevance or image-gallery presentation rules, belongs to each page's future `*IndexQuery` service, not to Epic 8 shared infrastructure.
- Dropdown option preloading remains a controller responsibility. This is already aligned with the Milestone 1 request-driven pattern and does not require a new shared abstraction.

## Outcome

With these Epic 8 extensions in place, the remaining migrations can keep a single canonical request-driven list pattern. Future stories only need entity-specific list definitions, query services, controller preloads, and Blade views; they do not need new infrastructure branches.
