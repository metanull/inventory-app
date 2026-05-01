---
layout: default
title: Importer Orientation
parent: Collaborator Guide
nav_order: 3
---

# Importer Orientation

The importer is a Node.js tool under `scripts/importer/`. It reads legacy databases, transforms source records, and writes Inventory records.

## Main structure

| Path | Purpose |
|---|---|
| `scripts/importer/src/cli/import.ts` | CLI entry point and importer registry. |
| `scripts/importer/src/importers/` | Importer phases and source-specific import classes. |
| `scripts/importer/src/domain/types/` | Legacy source row types. |
| `scripts/importer/src/domain/transformers/` | Source-to-Inventory transformation logic. |
| `scripts/importer/src/strategies/sql-strategy.ts` | SQL persistence strategy. |
| `scripts/importer/src/core/tracker.ts` | Imported-entity tracker for dependency resolution and deduplication. |
| `scripts/importer/src/tools/image-sync.ts` | Post-import image file synchronization. |

## Import phases

The CLI registry orders importers by declared dependencies. High-level phases are:

- reference data;
- core `mwnf3` content;
- images;
- Sharing History;
- glossary;
- timelines;
- media and documents;
- Explore;
- Travels;
- Thematic Galleries;
- final linking and cleanup.

## Transformation rules

Keep transformation behavior in transformer functions when possible. Transformers group denormalized source rows, normalize language and country codes, convert text, build `backward_compatibility` values, and prepare Inventory data structures.

Persistence belongs in the strategy. Importers coordinate reads, dependency lookups, transformation calls, writes, logging, and skips.

## Validation support

Every imported record that replaces a legacy row should keep a useful `backward_compatibility` value. This value is the main customer validation bridge described in [Legacy Import](../understanding/legacy-import).

## Image synchronization

The database import creates image rows first. The image sync tool then copies or symlinks files from legacy storage and updates final paths and sizes.

Do not judge physical image availability until image sync has run.
