---
layout: default
title: APIs and Documentation
parent: Collaborator Guide
nav_order: 4
---

# APIs and Documentation

This page explains the API and documentation surfaces that collaborators maintain.

## Management API

The current API is the authenticated management API. It supports read and write workflows for Inventory management and drives the generated TypeScript client.

Use [Management API Reference](../api/) for Swagger UI, OpenAPI JSON, and TypeScript client links.

## Read-only API

The read-only API is a planned future interface for public and lightweight front-end clients. It should optimize delivery, reduce client joins, and return content shaped for consumption instead of mirroring the management model.

Do not treat the current management API as the final read-only API.

## Generated client

The `api-client/` directory is generated from the OpenAPI specification. Do not edit generated client files by hand. Change the Laravel API and generated OpenAPI source, then regenerate the client.

## Jekyll documentation

The `docs/` directory is a Jekyll site. The primary manual documentation now lives in:

- `docs/understanding/` for customers and validation teams;
- `docs/collaborators/` for developers and technical collaborators.

Generated or reference documentation remains available in:

- `docs/api/`;
- `docs/api-client/`;
- `docs/_model/`;
- `docs/_docs/`.

## Documentation rule of thumb

Put business explanations in `understanding/`. Put code orientation in `collaborators/`. Link from collaborator pages back to understanding pages instead of duplicating model explanations.
