@{
    # Client package configuration
    PackageConfig = @{
        Name = '@metanull/inventory-app-api-client'
        Version = '1.0.1'  # Base version - will be auto-incremented
        Main = 'index.js'
        Types = 'index.d.ts'
        Description = 'TypeScript-Axios client for inventory-app API'
        Repository = 'https://github.com/metanull/inventory-app'
        Author = 'Pascal Havelange'
        License = 'MIT'
    }

    # Version management configuration
    Versioning = @{
        # Strategy: 'auto' (auto-increment), 'timestamp' (date-based), 'hash' (API spec hash), 'manual' (use base version)
        Strategy = 'auto'
        # For auto-increment: which part to increment (major, minor, patch)
        IncrementType = 'patch'
        # Include build metadata in version (e.g., 1.0.0+20250109.1234)
        # Build metadata will be converted to a unique version suffix during npm publish
        IncludeBuildMetadata = $true
        # Include pre-release identifier for development versions
        PreReleaseIdentifier = 'dev'
    }

    # File paths configuration
    Paths = @{
        OpenApiSpec = 'docs/_openapi/api.json'
        OutputDirectory = 'api-client'
        PackageJsonFile = 'package.json'
        ReadmeFile = 'README.md'
    }

    # OpenAPI Generator configuration
    Generator = @{
        Type = 'typescript-axios'
        Command = 'npx openapi-generator-cli generate'
    }

    # Templates
    Templates = @{
        PackageJson = @'
{{
  "name": "{0}",
  "version": "{1}",
  "main": "{2}",
  "types": "{3}",
  "description": "{4}",
  "repository": {{
    "type": "git",
    "url": "git+{5}.git"
  }},
  "author": "{6}",
  "license": "{7}"
}}
'@

        ReadmeContent = @'
# {0}

This is a generated TypeScript client for the inventory-app API using OpenAPI and Axios.

## Installation

```shell
npm install {1}
```

## Usage Example

```typescript
import {{ Configuration, DefaultApi }} from '{2}';

const api = new DefaultApi(new Configuration({{ basePath: 'https://your.api.url' }}));
api.addressIndex().then(response => console.log(response.data));
```

## Versioning

This client uses automatic versioning to track API changes:
- **Auto-increment**: Patch version increases with each generation
- **Development versions**: Include `-dev` suffix and build metadata
- **API changes**: When the OpenAPI spec changes, a new version is generated

To check for updates:
```shell
npm outdated {1}
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
cd .\{3}
npm publish --access public
```

---
This client is auto-generated. For customizations, edit the OpenAPI spec and regenerate.
'@
    }
}
