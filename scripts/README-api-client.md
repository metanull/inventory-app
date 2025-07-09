# API Client Generation Examples

## Basic Usage

```powershell
# Generate client with auto-incrementing version
.\scripts\generate-api-client.ps1

# Generate client with specific version
.\scripts\generate-api-client.ps1 -Version "2.0.0"

# Generate client without version increment
.\scripts\generate-api-client.ps1 -NoVersionIncrement

# Force regeneration of package.json and README
.\scripts\generate-api-client.ps1 -Force
```

## Configuration

Edit `scripts/api-client-config.psd1` to customize:

- **Package details**: Name, description, author, repository
- **Versioning strategy**: auto, timestamp, hash, or manual
- **Templates**: Customize package.json and README templates

## Versioning Strategies

1. **Auto**: Increments patch version each time (default)
2. **Timestamp**: Uses date/time for version (e.g., 1.0.0-20250109.1234)
3. **Hash**: Uses API spec hash for version (e.g., 1.0.0-a1b2c3d4)
4. **Manual**: Uses base version without incrementing

## Publishing

```powershell
# Publish to npm
.\scripts\publish-api-client.ps1

# Dry run to test
.\scripts\publish-api-client.ps1 -DryRun
```

## Integration with CI/CD

Add this to your GitHub Actions workflow:

```yaml
- name: Generate API Client
  run: .\scripts\generate-api-client.ps1
  
- name: Publish API Client
  run: .\scripts\publish-api-client.ps1
  env:
    NODE_AUTH_TOKEN: ${{ secrets.NPM_TOKEN }}
```
