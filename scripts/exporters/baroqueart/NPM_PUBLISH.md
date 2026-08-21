# NPM Package Publishing Guide

## Overview

The exporter now supports publishing data as a private npm package to GitHub Packages. This enables frontend developers to easily consume Islamic Art data without managing HTTP requests, authentication, or server uptime dependencies.

## Quick Start

### Step 1: Export and prepare the package

```bash
npm run export -- islamicart ISL --publish
```

This will:
1. Export all Islamic Art data to JSON files
2. Auto-increment the version in the `.version` file (starting at 1.0.0, then 1.0.1, 1.0.2, etc.)
3. Generate `package.json` with the bumped version
4. Generate `README.md` with usage instructions

### Step 2: Publish to GitHub Packages

```bash
cd output/islamicart
npm publish
```

The package is published to the private GitHub Packages registry and becomes available for installation.

## How It Works

### Version Management

Versions are stored in a `.version` file in the output directory:

```
output/islamicart/.version
```

**First run:** Creates version `1.0.0`  
**Second run:** Bumps to `1.0.1`  
**Third run:** Bumps to `1.0.2`  
And so on...

The version follows [Semantic Versioning](https://semver.org/) with patch bumps for data updates.

### Package Structure

When `--publish` is used, the output directory becomes a valid npm package:

```
output/islamicart/
├── package.json              ← Auto-generated with version
├── README.md                 ← Usage guide for consumers
├── .version                  ← Persisted version state
├── data/
│   ├── manifest.json
│   ├── manifest.json.gz
│   ├── items.json
│   ├── items.json.gz
│   ├── partners.json
│   ├── partners.json.gz
│   ├── collections.json
│   ├── collections.json.gz
│   ├── countries.json
│   ├── countries.json.gz
│   ├── dynasties.json
│   ├── dynasties.json.gz
│   ├── glossary.json
│   ├── glossary.json.gz
│   ├── languages.json
│   ├── languages.json.gz
│   └── translations/
│       ├── items.en.json
│       ├── items.ar.json
│       ├── items.fr.json
│       └── ...
```

### Package Metadata

The generated `package.json` includes:
- **name:** `@mwnf/islamicart-data` (or custom via `--package-name`)
- **version:** Auto-incremented semantic version
- **description:** Reflects the projects included
- **exports:** Points to the JSON data files
- **files:** Restricts npm publish to necessary files (data/, README.md)

### GitHub Packages Authentication

Before publishing, ensure GitHub authentication is configured in `~/.npmrc`:

```bash
echo "//npm.pkg.github.com/:_authToken=YOUR_GITHUB_TOKEN" >> ~/.npmrc
echo "@mwnf:registry=https://npm.pkg.github.com" >> ~/.npmrc
```

For more details, see [GitHub Packages npm documentation](https://docs.github.com/en/packages/working-with-a-npm-registry/working-with-the-npm-registry#authenticating-with-a-personal-access-token).

## Usage Options

### Default package name (recommended)

Package name is derived from the subdirectory:

```bash
npm run export -- islamicart ISL --publish
# Creates: @mwnf/islamicart-data
```

### Custom package name

Override the package name if needed:

```bash
npm run export -- islamicart ISL --publish --package-name @mwnf/islamic-art-data
# Creates: @mwnf/islamic-art-data
```

### Multiple projects in one package

Export multiple projects and publish together:

```bash
npm run export -- combined ISL WHS --publish --package-name @mwnf/mwnf-data
# Includes both ISL and WHS projects in one package
```

### Keeping JSON and .gz files

The exporter always creates both `.json` and `.json.gz` files in the output. The npm package includes **only the uncompressed `.json` files** (see `files` in package.json).

The `.gz` files remain in the output directory and can be used for CDN distribution or other purposes in the future.

## Consumer Usage

Once published, frontend developers can use the package like this:

### Installation

```bash
npm install @mwnf/islamicart-data
```

### Import items

```javascript
import items from '@mwnf/islamicart-data/data/items.json' assert { type: 'json' }

items.forEach(item => {
  console.log(item.id, item.type, item.translations)
})
```

### Import all data

```javascript
import manifest from '@mwnf/islamicart-data/data/manifest.json' assert { type: 'json' }
import items from '@mwnf/islamicart-data/data/items.json' assert { type: 'json' }
import partners from '@mwnf/islamicart-data/data/partners.json' assert { type: 'json' }
import collections from '@mwnf/islamicart-data/data/collections.json' assert { type: 'json' }
import dynasties from '@mwnf/islamicart-data/data/dynasties.json' assert { type: 'json' }
import countries from '@mwnf/islamicart-data/data/countries.json' assert { type: 'json' }
import glossary from '@mwnf/islamicart-data/data/glossary.json' assert { type: 'json' }
import languages from '@mwnf/islamicart-data/data/languages.json' assert { type: 'json' }
```

## Workflow

### Initial setup (one-time)

1. Configure GitHub authentication in `~/.npmrc` (see above)
2. Export and publish the first version:
   ```bash
   npm run export -- islamicart ISL --publish
   cd output/islamicart
   npm publish
   ```
3. Frontend developer installs: `npm install @mwnf/islamicart-data`

### Routine updates

1. Update content in the Filament admin or inventory database
2. Re-export and republish:
   ```bash
   npm run export -- islamicart ISL --publish --force
   cd output/islamicart
   npm publish
   ```
3. Frontend developer updates: `npm update @mwnf/islamicart-data`

## Troubleshooting

### `npm publish` fails with "not authorized"

Make sure `~/.npmrc` is configured with a valid GitHub token:

```bash
cat ~/.npmrc | grep "npm.pkg.github.com"
```

If missing, add it (see [GitHub Packages Authentication](#github-packages-authentication)).

### Version file is missing or corrupted

Delete it and re-export. It will be recreated:

```bash
rm output/islamicart/.version
npm run export -- islamicart ISL --publish --force
```

### Package already published with this version

This means the version was already published. The next `--publish` run will auto-increment:

```bash
npm run export -- islamicart ISL --publish --force
# Version is now 1.0.X (next patch)
npm publish
```

## Advanced: Manual version control

If you want to manually control the version instead of auto-incrementing:

1. Edit `.version` directly:
   ```bash
   echo "2.0.0" > output/islamicart/.version
   ```

2. Regenerate package.json without re-exporting:
   ```
   (Not yet supported — create a feature request if needed)
   ```

For now, re-run the full export with `--publish` to regenerate.

## Notes

- The `.version` file is **not** ignored by git — you may want to commit it for audit trail
- The `package.json` and `README.md` are **regenerated** on every `--publish` run
- Only `.json` files are included in the published npm package; `.gz` files are for future CDN use
- The package is **private** (via GitHub Packages registry); only authenticated users can install it
