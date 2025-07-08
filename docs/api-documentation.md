---
layout: default
title: API Documentation
nav_order: 1
---

# API Documentation

This page provides interactive documentation for the Inventory Management API using Swagger UI.

## Quick Links

- [Download OpenAPI JSON Specification]({{ '/api.json' | relative_url }})
- [Interactive API Documentation (Swagger UI)]({{ '/swagger-ui.html' | relative_url }})

## Interactive Documentation

<iframe src="{{ '/swagger-ui.html' | relative_url }}" width="100%" height="800px" frameborder="0" style="border: 1px solid #ddd; border-radius: 4px;"></iframe>

## About the API

The Inventory Management API provides RESTful endpoints for managing museum inventory data. This documentation is automatically generated from the OpenAPI specification and updated with each deployment.

### Key Features

- **Complete CRUD Operations** - Full Create, Read, Update, Delete functionality for all models
- **Polymorphic Picture System** - Attach images to Items, Details, and Partners
- **Image Processing Pipeline** - Upload, process, and attach images with automatic optimization
- **Markdown Support** - Convert and validate markdown content
- **Mobile Authentication** - Token-based authentication for mobile applications
- **Internationalization** - Multi-language support with default language management

### Picture Attachment Workflow

1. **Upload**: Images are uploaded via `POST /api/image-upload` and processed asynchronously
2. **Processing**: Background events resize, validate, and optimize images
3. **Available Pool**: Successfully processed images become `AvailableImage` records
4. **Attachment**: Images are attached to models via transactional operations:
    - `POST /api/picture/attach-to-item/{item}` - Attach to Items
    - `POST /api/picture/attach-to-detail/{detail}` - Attach to Details
    - `POST /api/picture/attach-to-partner/{partner}` - Attach to Partners
5. **Management**: Attached images become `Picture` records with full CRUD operations

### Features

- Interactive API testing directly from the documentation
- Detailed request/response examples
- Schema definitions and validation rules
- Authentication and authorization information
- File upload and download capabilities
- Polymorphic relationship management
