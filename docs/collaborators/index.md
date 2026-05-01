---
layout: default
title: Collaborator Guide
nav_order: 3
has_children: true
permalink: /collaborators/
---

# Collaborator Guide

This section orients developers and technical collaborators in the current codebase. It points back to the customer-facing model documentation to keep business concepts in one place.

## Read first

Start with [Inventory Principles](../understanding/inventory-principles) and [Core Model](../understanding/core-model). Those pages define the terms used in code and tests.

Then use these pages by task:

- [Codebase Map](codebase-map) for repository structure.
- [Filament Back-Office](filament-admin) for `/admin` work.
- [Importer Orientation](importer) for legacy migration work.
- [APIs and Documentation](apis-and-docs) for management API, OpenAPI, generated client, and Jekyll documentation work.
- [Development Workflow](development-workflow) for setup and validation commands.

## Current development posture

Filament 3 is the main UI. The older `/web` Blade/Livewire back-office is legacy and scheduled for removal. New back-office work belongs in `/admin` unless an issue explicitly says otherwise.

The management API remains maintained. The read-only API is a future design area and should not be confused with the current authenticated management API.
