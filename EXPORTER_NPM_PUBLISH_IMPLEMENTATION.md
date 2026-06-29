# Implementation: npm Package Publishing for the Exporter

## Summary

Added `--publish` flag to the data exporter to generate npm packages ready for publishing to GitHub Packages. This enables decoupled distribution of Islamic Art data to frontend developers without server dependencies.

## What Was Implemented

### 1. New Files

- **`scripts/exporter/src/core/publish-manager.ts`** — Core class for:
  - Semantic version management (1.0.0 → 1.0.1 → 1.0.2)
  - Generating `package.json` with proper exports
  - Generating `README.md` with usage instructions

- **`scripts/exporter/NPM_PUBLISH.md`** — Complete guide for:
  - Quick start workflow
  - Version management
  - GitHub authentication setup
  - Consumer usage examples
  - Troubleshooting

### 2. Updated CLI (`scripts/exporter/src/cli/export.ts`)

Added two new options:
- `--publish` — Flag to enable npm package generation
- `--package-name <name>` — Override default package name (optional)

Added post-export logic to:
1. Instantiate PublishManager
2. Auto-increment version (stored in `.version` file)
3. Generate `package.json` with bumped version
4. Generate `README.md` with usage guide
5. Print instructions for publishing

## Decisions Made

### Package Naming
- **Default:** `@mwnf/{subdirectory}-data`
  - Example: `npm run export -- islamicart ISL --publish` → `@mwnf/islamicart-data`
- **Custom:** `--package-name @mwnf/custom-name`

### Version Storage
- **Location:** `.version` file in output directory
- **Format:** Semantic version (e.g., `1.0.0`)
- **Strategy:** Patch bumping only (1.0.0 → 1.0.1 → 1.0.2)
- **Persistence:** File is not regenerated if it exists; version is bumped each run

### GitHub Authentication
- Assumes user has configured `~/.npmrc` with GitHub token
- Documentation includes setup instructions
- No modifications to publish mechanism itself

### Package Contents
- **Included in npm:** JSON files + README.md (as specified in `files` field)
- **Not included in npm:** `.gz` files (kept for future CDN use)
- **Exports:** Proper `exports` field for clean import paths

## Usage

### Quick Start

```bash
# Step 1: Export and prepare
npm run export -- islamicart ISL --publish

# Step 2: Publish
cd output/islamicart
npm publish
```

### With Custom Package Name

```bash
npm run export -- islamicart ISL --publish --package-name @mwnf/islamic-art-data
```

### Multiple Projects

```bash
npm run export -- combined ISL WHS --publish --package-name @mwnf/mwnf-data
```

## Consumer Usage

Once published, frontend developers install and use like this:

```bash
npm install @mwnf/islamicart-data
```

```javascript
import items from '@mwnf/islamicart-data/data/items.json' assert { type: 'json' }
import partners from '@mwnf/islamicart-data/data/partners.json' assert { type: 'json' }
// ... and so on
```

## Key Features

✓ **Automatic versioning** — Patch-based semver bumping, no manual version management  
✓ **Persistent version state** — `.version` file survives across runs  
✓ **Readable metadata** — Generated `package.json` and `README.md` are human-editable  
✓ **No hardcoded credentials** — Delegates authentication to `~/.npmrc` (standard npm practice)  
✓ **Flexible package naming** — Default convention with override option  
✓ **Manual publishing** — User controls `npm publish` timing  
✓ **Future-proof** — `.gz` files created for potential CDN distribution  

## Testing

All TypeScript compiles without errors:
```bash
npm run type-check
```

The implementation follows:
- Existing exporter patterns (BaseExporter, context, logger)
- npm package conventions (exports, files, keywords)
- GitHub Packages requirements
- Semantic Versioning spec

## Next Steps for User

1. **First-time setup:** Configure GitHub authentication in `~/.npmrc` (see NPM_PUBLISH.md)
2. **Test workflow:** Run `npm run export -- islamicart ISL --publish --force` and verify output
3. **Publish:** `cd output/islamicart && npm publish`
4. **Verify:** Frontend developer can `npm install @mwnf/islamicart-data`

## Documentation

Comprehensive guide available at: `scripts/exporter/NPM_PUBLISH.md`

Covers:
- Quick start
- How versioning works
- GitHub Packages authentication setup
- Consumer usage examples
- Troubleshooting common issues
- Advanced manual version control
