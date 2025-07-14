---
layout: default
title: API Documentation
nav_order: 2
---

# API Documentation

This page provides interactive documentation for the Inventory Management API using Swagger UI.

## Quick Links

- [Download OpenAPI JSON Specification]({{ '/api.json' | relative_url }})
- [Interactive API Documentation (Swagger UI)]({{ '/swagger-ui.html' | relative_url }})
- [TypeScript API Client Documentation]({{ '/api-client/' | relative_url }})

## Interactive Documentation

<iframe src="{{ '/swagger-ui.html' | relative_url }}" width="100%" height="800px" frameborder="0" style="border: 1px solid #ddd; border-radius: 4px;"></iframe>

## About the API

The Inventory Management API provides RESTful endpoints for managing museum inventory data. This documentation is automatically generated from the OpenAPI specification and updated with each deployment.

### Key Features

- **Complete CRUD Operations** - Full Create, Read, Update, Delete functionality for all models
- **Application Information** - Health check and version endpoints for monitoring:
  - `GET /api/info` - Complete application information with health status
  - `GET /api/health` - Health check endpoint for monitoring systems
  - `GET /api/version` - Application version information
- **Collection System** - Three types of collections with comprehensive translation support:
  - **Collections** - Traditional item groupings with partner relationships
  - **Galleries** - Flexible polymorphic collections for Items and Details
  - **Exhibitions** - Hierarchical theme-based picture galleries with two-level theme structure
- **Polymorphic Picture System** - Attach images to Items, Details, Partners, and Themes
- **Hierarchical Theme Organization** - Support for main themes and subthemes in exhibitions
- **Multi-Language Translation** - Complete internationalization for all collection types and themes
- **Image Processing Pipeline** - Upload, process, and attach images with automatic optimization
- **Markdown Support** - Convert and validate markdown content
- **Mobile Authentication** - Token-based authentication for mobile applications
- **Partner Management** - Support for different contribution levels (Partner, Associated Partner, Minor Contributor)

### Picture Attachment & Detachment Workflow

1. **Upload**: Images are uploaded via `POST /api/image-upload` and processed asynchronously
2. **Processing**: Background events resize, validate, and optimize images
3. **Available Pool**: Successfully processed images become `AvailableImage` records
4. **Attachment**: Images are attached to models via transactional operations:
   - `POST /api/picture/attach-to-item/{item}` - Attach to Items
   - `POST /api/picture/attach-to-detail/{detail}` - Attach to Details
   - `POST /api/picture/attach-to-partner/{partner}` - Attach to Partners
   - Exhibition themes can contain pictures from both Items and Details
5. **Management**: Attached images become `Picture` records with full CRUD operations
6. **Detachment**: Pictures can be detached and converted back to AvailableImages:
   - `DELETE /api/picture/{picture}/detach-from-item/{item}` - Detach from Items
   - `DELETE /api/picture/{picture}/detach-from-detail/{detail}` - Detach from Details
   - `DELETE /api/picture/{picture}/detach-from-partner/{partner}` - Detach from Partners

### Features

- Interactive API testing directly from the documentation
- Detailed request/response examples
- Schema definitions and validation rules
- Authentication and authorization information
- File upload and download capabilities
- Polymorphic relationship management

## TypeScript API Client

The project provides an auto-generated TypeScript-Axios client library for seamless API integration:

### Installation

```bash
npm install @metanull/inventory-app-api-client@latest
```

### Quick Start

```typescript
import { Configuration, DefaultApi } from "@metanull/inventory-app-api-client";

const api = new DefaultApi(
  new Configuration({ basePath: "https://your.api.url" }),
);
api.addressIndex().then((response) => console.log(response.data));
```

### Package Information

- **Package**: [`@metanull/inventory-app-api-client`](https://github.com/metanull/inventory-app/packages)
- **Latest Version**: `@metanull/inventory-app-api-client@1.1.8-dev.709.2313`
- **Registry**: [GitHub Packages](https://npm.pkg.github.com/)
- **Auto-generated**: Updated automatically with each API change
- **Type-safe**: Full TypeScript support with comprehensive type definitions
- **Exhibition Support**: Includes full TypeScript types for Exhibition and Theme APIs

### Client Documentation

The TypeScript client includes comprehensive documentation for all API endpoints:

- **[TypeScript API Client Documentation](api-client/)** - Auto-generated documentation for all client methods
- Method signatures and parameters
- Response type definitions
- Usage examples for each endpoint
- Error handling patterns

### Generation Process

The client is automatically generated using:

- [OpenAPI Generator CLI](https://github.com/OpenAPITools/openapi-generator-cli)
- Published to [GitHub Packages](https://npm.pkg.github.com/)
- Versioned automatically with each API update
- Includes comprehensive documentation and examples

## API Client Development Guide

This section provides comprehensive information about the API client generation, versioning, publishing, and integration process.

### Project Scripts

The project includes several PowerShell scripts for managing the API client lifecycle:

#### 1. `generate-api-client.ps1`

This script generates the TypeScript API client from the OpenAPI specification.

**Location**: `scripts/generate-api-client.ps1`

**Usage**:

```powershell
# Make output more verbose
$InformationPreference = 'Continue'

# Basic usage - generates with default settings (patch increment)
.\scripts\generate-api-client.ps1

# Force regeneration of package.json and README.md
.\scripts\generate-api-client.ps1 -Force

# Specify version increment type (major, minor, patch)
.\scripts\generate-api-client.ps1 -IncrementType major
.\scripts\generate-api-client.ps1 -IncrementType minor
.\scripts\generate-api-client.ps1 -IncrementType patch

# Use explicit version number
.\scripts\generate-api-client.ps1 -Version "2.0.0"

# Skip version incrementing
.\scripts\generate-api-client.ps1 -NoVersionIncrement
```

**Parameters**:

- `-Force`: Overwrites existing package.json and README.md files
- `-Version`: Sets a specific version number, overriding automatic versioning
- `-NoVersionIncrement`: Uses the base version without incrementing
- `-IncrementType`: Specifies which part of version to increment ('major', 'minor', or 'patch')

#### 2. `publish-api-client.ps1`

This script publishes the generated API client to the npm registry.

**Location**: `scripts/publish-api-client.ps1`

**Usage**:

```powershell
# Perform a dry run (no actual publishing)
.\scripts\publish-api-client.ps1 -DryRun

# Publish to GitHub Packages with credentials
.\scripts\publish-api-client.ps1 -Credential (Get-Secret github-package)

# Publish to a different registry
.\scripts\publish-api-client.ps1 -Registry "https://registry.npmjs.org/"
```

**Parameters**:

- `-DryRun`: Tests the publishing process without actually publishing
- `-Registry`: Specifies the npm registry URL (default: GitHub Packages)
- `-Credential`: Provides authentication credentials for the registry

### Versioning Strategy

The API client uses semantic versioning with the following components:

1. **Base Version** (`MAJOR.MINOR.PATCH`):
   - MAJOR: Incremented for incompatible API changes
   - MINOR: Incremented for backward-compatible new features
   - PATCH: Incremented for backward-compatible bug fixes

2. **Pre-release Identifier** (`-dev`):
   - Indicates a development version not intended for production

3. **Build Metadata** (`+yyyyMMdd.HHmm`):
   - Timestamp that ensures each build has a unique identifier
   - Format: year, month, day, hour, minute

**Example Version**: `1.2.3-dev+20250709.1347`

When publishing to npm, build metadata is converted to a compatible format:
`1.2.3-dev.0709.1347`

### Configuration

The API client configuration is stored in `scripts/api-client-config.psd1` and includes:

- Package metadata (name, description, author, license)
- Versioning strategy and settings
- File paths for input/output
- Templates for package.json and README.md

### API Client Generation Process

1. **OpenAPI Specification**: The API client is generated from the OpenAPI specification at `docs/_openapi/api.json`

2. **Generation**: The `generate-api-client.ps1` script:
   - Reads the OpenAPI specification
   - Determines the version number based on configuration
   - Runs the OpenAPI Generator CLI
   - Creates or updates package.json and README.md

3. **Versioning**: Version numbers are determined by:
   - Reading the existing version from package.json
   - Incrementing according to the specified strategy
   - Adding pre-release identifier and build metadata
   - Generating a unique version for each build

4. **Output**: The generated client is placed in the `api-client` directory

### Publishing Process

1. **Preparation**: The `publish-api-client.ps1` script:
   - Validates that the client exists
   - Reads version information
   - Handles npm authentication
   - Converts the version for npm compatibility

2. **Publication**: The package is published to GitHub Packages by default
   - Pre-release versions are tagged as 'dev'
   - Build metadata is converted to npm-compatible format
   - The publish command uses appropriate access settings

3. **Authentication**: Authentication with GitHub Packages requires:
   - A GitHub personal access token with appropriate permissions
   - Configuration in the user's .npmrc file or via credentials

### Integration in Client Projects

To use the API client in a project:

1. **Authentication Setup**:

   Create or update `.npmrc` in your project root:

   ```
   @metanull:registry=https://npm.pkg.github.com
   //npm.pkg.github.com/:_authToken=${GITHUB_TOKEN}
   ```

   Where `${GITHUB_TOKEN}` is your GitHub personal access token with package read permissions.

2. **Installation**:

   ```bash
   # Latest stable version
   npm install @metanull/inventory-app-api-client

   # Latest development version
   npm install @metanull/inventory-app-api-client@dev

   # Specific version
   npm install @metanull/inventory-app-api-client@1.2.3
   ```

3. **Usage**:

   ```typescript
   import {
     Configuration,
     DefaultApi,
   } from "@metanull/inventory-app-api-client";

   // Create API instance
   const api = new DefaultApi(
     new Configuration({
       basePath: "https://your-api-url",
       // Optional: Authentication
       accessToken: "your-access-token",
     }),
   );

   // Make API calls
   async function fetchItems() {
     try {
       const response = await api.itemIndex();
       return response.data;
     } catch (error) {
       console.error("API Error:", error);
       throw error;
     }
   }
   ```

### Troubleshooting

Common issues and their solutions:

- **Version Conflicts**: If npm reports a version conflict when publishing:
  - Use `.\scripts\generate-api-client.ps1 -IncrementType minor` to increment the version
  - Run `.\scripts\publish-api-client.ps1 -DryRun` to verify the new version

- **Authentication Issues**: If publishing fails with authorization errors:
  - Verify your GitHub token has the `write:packages` scope
  - Check that your .npmrc file is correctly configured
  - Try running `npm login --registry=https://npm.pkg.github.com/` manually

- **Generation Issues**: If client generation fails:
  - Validate the OpenAPI specification with a tool like Swagger Editor
  - Check for errors in the OpenAPI JSON file
  - Try running with verbose logging: `.\scripts\generate-api-client.ps1 -Verbose`

- **Script Output Visibility**: If you don't see informational messages:
  - Use the `-InformationPreference Continue` parameter to ensure information stream is visible
  - Example: `.\scripts\generate-api-client.ps1`
  - Note: The scripts currently use `-InformationAction Continue` internally which may be overridden by your session's preferences

### Best Practices

1. **Version Management**:
   - Use `-IncrementType major` for breaking API changes
   - Use `-IncrementType minor` for new endpoints or parameters
   - Use `-IncrementType patch` for bug fixes and minor updates

2. **Client Updates**:
   - Regenerate the client after any API changes
   - Run tests against the new client before publishing
   - Document breaking changes in release notes

3. **Package Consumption**:
   - Pin to specific versions in production applications
   - Use `@latest` for development/testing purposes
   - Consider using GitHub Actions to automate client updates
