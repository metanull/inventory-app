---
layout: default
title: API Client Documentation Generator
nav_order: 2
parent: Backend Guidelines
---

# API Client Documentation Generator

Automatic generation of Jekyll-compatible documentation for the TypeScript API client.

## Files

- `generate-client-docs.py` - Python script for generating client documentation
- `.github/workflows/github-pages.yml` - GitHub Actions workflow including client docs generation
- `api-client/docs/` - Directory containing generated TypeScript client documentation
- `docs/api-client/` - Directory containing Jekyll-compatible client documentation pages

## How it Works

1. **Client Generation**: The TypeScript API client is generated using:
   - OpenAPI Generator CLI
   - PowerShell script `scripts/generate-api-client.ps1`
   - Automatically creates markdown documentation files
2. **Documentation Processing**: The Python script:
   - Processes generated markdown files from the client
   - Categorizes files by type (APIs, Models, Requests, Responses, Other)
   - Creates Jekyll-compatible pages with proper front matter
   - Fixes broken navigation links
   - Generates a comprehensive index page
3. **Site Building**: Jekyll builds the static site including client documentation
4. **Deployment**: Automatically deploys to GitHub Pages

## Key Features

- **Categorized Documentation**: Files are organized by type:
  - **APIs**: API endpoint documentation (26 items)
  - **Models**: Data model documentation (24 items)
  - **Requests**: Request object documentation (39 items)
  - **Responses**: Response object documentation (48 items)
  - **Other**: Miscellaneous documentation (9 items)
- **Automated Processing**: No manual intervention required
- **Link Fixing**: Automatically fixes broken navigation links in generated documentation
- **Jekyll Integration**: Generated pages are fully compatible with Jekyll and GitHub Pages

## Output Structure

```
docs/api-client/
├── index.md                           # Main index with all categories
├── addressapi.md                      # API endpoint documentation
├── addressresource.md                 # Model documentation
├── addressstorerequest.md             # Request documentation
├── addressstore201response.md         # Response documentation
└── ...
```

## File Naming Convention

- Original camelCase names are converted to lowercase with hyphens
- Files maintain their original structure but with Jekyll front matter
- Navigation links are updated to work with Jekyll's site structure

## Categories

### APIs (26 items)

API endpoint documentation including:

- Method signatures and parameters
- Request/response examples
- Authentication requirements
- HTTP status codes and error handling

### Models (24 items)

Data model documentation including:

- Property definitions
- Type information
- Validation rules
- Relationships between models

### Requests (39 items)

Request object documentation including:

- Required and optional parameters
- Data types and validation
- Example request bodies
- Parameter descriptions

### Responses (48 items)

Response object documentation including:

- Response structure
- Status codes
- Error responses
- Success response examples

### Other (9 items)

Miscellaneous documentation including:

- Inline objects
- Translation structures
- Utility types
- Helper objects

## Configuration

The generator uses configuration defined in `generate-client-docs.py`:

```python
# Configuration
CLIENT_DOCS_DIR = "api-client/docs"
JEKYLL_CLIENT_DIR = "docs/api-client"
LOG_FILE = "docs/client-docs.log"
```

## Link Fixing

The generator automatically fixes common broken links:

- `[Back to top](#)` - Links to page top
- `[Back to API list](../README.md#documentation-for-api-endpoints)` → `[Back to API list]({{ site.baseurl }}/api-client/)`
- `[Back to Model list](../README.md#documentation-for-models)` → `[Back to Model list]({{ site.baseurl }}/api-client/)`
- `[Back to README](../README.md)` → `[Back to README]({{ site.baseurl }}/api-client/)`

## Usage

### Manual Generation

```bash
# Generate the TypeScript client first
# . ./scripts/generate-api-client.ps1

# Generate Jekyll documentation
python scripts/generate-client-docs.py
```

### Automated Generation

The client documentation is automatically generated as part of the GitHub Pages workflow:

```yaml
- name: Generate API Client Documentation
  run: |
    if [ -d "api-client/docs" ]; then
      python scripts/generate-client-docs.py
    fi
```

## Integration with GitHub Pages

The generated documentation is automatically included in the GitHub Pages build process:

1. **Client Generation**: TypeScript client is generated with documentation
2. **Documentation Processing**: Python script processes and categorizes files
3. **Jekyll Build**: Jekyll processes the generated markdown files
4. **Deployment**: Documentation is available at `/api-client/`

## Error Handling

The generator includes comprehensive error handling:

- Validates that required directories exist
- Checks for markdown files before processing
- Logs all operations for debugging
- Gracefully handles missing or malformed files

## Logging

All operations are logged to `docs/client-docs.log` with timestamps and severity levels:

- INFO: Normal operations
- WARNING: Non-fatal issues
- ERROR: Fatal errors that prevent generation

## Example Output

Generated pages include:

- Proper Jekyll front matter
- Category classification
- Fixed navigation links
- Consistent formatting
- Automatic timestamps

The main index provides:

- Installation instructions
- Usage examples
- Complete categorized listing of all documentation
- Package information
- Generation timestamps

This automation ensures that the TypeScript API client documentation is always up-to-date and properly integrated with the main documentation site.
