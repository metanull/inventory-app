---
layout: default
title: Validation Guide
parent: Understanding the Inventory
nav_order: 4
---

# Validation Guide

Use this guide when you compare imported Inventory records with legacy data. The aim is to validate content meaning, traceability, and relationships.

## General method

1. Start from the Inventory record you want to validate.
2. Find its `backward_compatibility` value.
3. Use the value to identify the legacy source table and key values.
4. Compare the Inventory record with the source rows.
5. Check translations by language and context.
6. Check images after image synchronization has run.
7. Check relationships through Collections, Item Links, Tags, and parent-child Items.

## Item checks

Confirm that:

- the Item type matches the source content;
- object and monument identity matches the legacy composite key;
- details are children of the correct monument;
- dates, owner reference, MWNF reference, country, project, and partner are sensible after mapping;
- expected translations exist where the source has usable text;
- residual source fields appear in `extra` when the importer preserves them there.

## Partner checks

Confirm that:

- museums, institutions, and schools map to Partner records;
- public names and descriptions appear as Partner Translations;
- country and coordinates match the source where available;
- partner images and logos attach to the correct Partner;
- the partner-to-monument link exists when the legacy museum record includes a monument reference.

## Collection checks

Confirm that:

- projects, exhibitions, galleries, themes, trails, itineraries, countries, regions, and locations appear as Collections;
- hierarchy follows the editorial source structure;
- Items attach to the correct Collections;
- contextual descriptions appear as translations in the expected context.

## Image checks

Confirm that:

- item pictures attach to the expected parent content;
- each imported object, monument, or detail picture also has a child Item of type `picture`;
- partner images and logos attach to the correct Partner;
- collection, contributor, and timeline images attach to the correct parent record;
- binary files are checked after image synchronization, not immediately after database import.

## Link and tag checks

Confirm that:

- direct related-content relationships appear as Item Links;
- exhibition, gallery, theme, itinerary, region, and location placement appears through Collections;
- keywords, materials, filters, and gallery labels appear as Tags;
- relationship explanations appear in translations when the source provides them.

## Glossary and timeline checks

Confirm that:

- glossary words, definitions, and spellings are present;
- imported spellings are preserved even when they need later editorial cleanup;
- HCR events are grouped under the expected Timeline;
- event translations, item links, bibliography data, and images are present where the source provides them.

## Log review

The importer logs progress, skipped records, warnings, and errors.

- A skipped duplicate usually means the importer protected the Inventory from creating the same record twice.
- A warning means the importer finished the run but found a data quality issue.
- An error means the importer did not import that record or stage correctly.

Use the Inventory record and the import log together. The record shows what landed. The log explains what the importer accepted, skipped, or rejected.
