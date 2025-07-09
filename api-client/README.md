# @metanull/inventory-app-api-client

This is a generated TypeScript client for the inventory-app API using OpenAPI and Axios.

## Installation

```shell
npm install @metanull/inventory-app-api-client
```

## Usage Example

```typescript
import { Configuration, DefaultApi } from '@metanull/inventory-app-api-client';

const api = new DefaultApi(new Configuration({ basePath: 'https://your.api.url' }));
api.addressIndex().then(response => console.log(response.data));
```

## Versioning

This client uses automatic versioning to track API changes:
- **Auto-increment**: Patch version increases with each generation
- **Development versions**: Include `-dev` suffix and build metadata
- **API changes**: When the OpenAPI spec changes, a new version is generated

To check for updates:
```shell
npm outdated @metanull/inventory-app-api-client
```

## Regeneration

To regenerate the client after updating the OpenAPI spec, run:

```powershell
# Generate with auto-incrementing patch version (default)
.\scripts\generate-api-client.ps1

# Increment major or minor version
.\scripts\generate-api-client.ps1 -IncrementType major
.\scripts\generate-api-client.ps1 -IncrementType minor

# Generate with specific version
.\scripts\generate-api-client.ps1 -Version "2.0.0"

# Generate without version increment
.\scripts\generate-api-client.ps1 -NoVersionIncrement
```

## Publishing

```powershell
cd .\api-client
npm publish --access public
```

---
This client is auto-generated. For customizations, edit the OpenAPI spec and regenerate.
