---
layout: default
title: API Documentation
nav_order: 2
has_children: true
permalink: /api/
---

# API Documentation

The REST API lets external applications read and manage inventory data programmatically. All endpoints require authentication via a Sanctum bearer token, except for health and version checks.

## Quick Access

- [Interactive API Explorer (Swagger UI)]({{ '/swagger-ui.html' | relative_url }}) — Browse and test endpoints directly
- [OpenAPI Specification]({{ '/api.json' | relative_url }}) — Download the full specification (JSON)
- [TypeScript Client Documentation]({{ '/api-client/' | relative_url }}) — Auto-generated client reference

## Interactive Documentation

<iframe src="{{ '/swagger-ui.html' | relative_url }}" width="100%" height="800px" frameborder="0" style="border: 1px solid #ddd; border-radius: 4px;"></iframe>

## What the API Offers

- **Full CRUD** on all inventory entities (items, partners, collections, projects, etc.)
- **Multi-language translations** — create and retrieve content in any language and audience context
- **Image management** — upload, process, and attach images to items, collections, and partners
- **Hierarchical collections** — organise items into exhibitions, galleries, thematic trails, and more
- **Tags** — flexible, ad-hoc categorisation of items
- **Search and pagination** — filter and page through large result sets

### System Endpoints

- `GET /api/info` — Application information
- `GET /api/health` — Health check (no auth required)
- `GET /api/version` — Current application version (no auth required)

### Image Workflow

Images flow through a three-stage pipeline:

1. **Upload** → `POST /api/image-upload` — upload a file for processing
2. **Processing** → the system resizes and optimises the image in the background
3. **Attachment** → attach the processed image to an item, collection, or partner

See the [Core Concepts — Image Management]({{ '/concepts#image-management' | relative_url }}) section for details.

## TypeScript API Client

An auto-generated TypeScript client is available as an npm package. It provides type-safe access to every API endpoint.

### Installation

```bash
npm install @metanull/inventory-app-api-client@latest
```

{: .note }

> The package is published to [GitHub Packages](https://github.com/metanull/inventory-app/packages). You need a GitHub PAT with `read:packages` scope and an `.npmrc` pointing to the GitHub registry. See the [README](https://github.com/metanull/inventory-app#using-the-api-client-external-developers) for setup details.

### Quick Example

```typescript
import { Configuration, DefaultApi } from "@metanull/inventory-app-api-client";

const api = new DefaultApi(
  new Configuration({ basePath: "https://your.api.url" }),
);
const items = await api.itemIndex();
console.log(items.data);
```

### Client Documentation

- **[TypeScript API Client Reference](api-client/)** — Auto-generated documentation for all client methods, parameters, and response types

### For Maintainers

The client is auto-generated from the [OpenAPI specification]({{ '/api.json' | relative_url }}) using `openapi-generator-cli`. Generation and publishing scripts are documented in [Development / Scripts]({{ '/development/scripts' | relative_url }}).
