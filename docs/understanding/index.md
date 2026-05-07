---
layout: default
title: Understanding the Inventory
nav_order: 2
has_children: true
permalink: /understanding/
---

# Understanding the Inventory

This section explains MWNF Inventory for customers, content owners, and validation teams. It avoids implementation detail and focuses on the decisions that matter when you compare legacy data with imported Inventory records.

## Recommended reading order

1. [Inventory Principles](inventory-principles) explains why the Inventory exists and what belongs in it.
2. [Core Model](core-model) explains the main content concepts and how they relate.
3. [Legacy Import](legacy-import) explains how source data moves into the new model.
4. [Validation Guide](validation-guide) gives practical checks for import review.
5. [Legacy URL Mapping](legacy-url-mapping) explains how imported records link back to legacy public and back-office URLs.
6. [System Architecture](system-architecture) explains how the back-office, APIs, importer, and documentation fit together.

## Current project focus

The current work focuses on validating imported legacy content. Validation does not require the new database to look like the old databases. It requires checking that the imported content keeps the correct meaning, relationships, translations, images, and traceability.

Use the `backward_compatibility` value on imported records as the main bridge from Inventory records back to legacy source rows.
