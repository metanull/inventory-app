import { readFileSync, writeFileSync, existsSync } from 'fs'
import { resolve } from 'path'
import { Logger } from './logger.js'

export interface PublishConfig {
  outputDir: string
  packageName: string
  projectKeys: string[]
  logger: Logger
}

export interface SemanticVersion {
  major: number
  minor: number
  patch: number
}

export class PublishManager {
  private config: PublishConfig
  private versionFile: string

  constructor(config: PublishConfig) {
    this.config = config
    this.versionFile = resolve(config.outputDir, '.version')
  }

  /**
   * Parse a semantic version string (e.g., "1.2.3") into components.
   */
  private parseVersion(versionString: string): SemanticVersion {
    const parts = versionString.split('.')
    if (parts.length !== 3) {
      throw new Error(`Invalid version format: ${versionString} (expected X.Y.Z)`)
    }
    const [major, minor, patch] = parts.map(Number)
    if (isNaN(major) || isNaN(minor) || isNaN(patch)) {
      throw new Error(`Invalid version format: ${versionString} (parts must be numeric)`)
    }
    return { major, minor, patch }
  }

  /**
   * Format semantic version components back to string.
   */
  private formatVersion(v: SemanticVersion): string {
    return `${v.major}.${v.minor}.${v.patch}`
  }

  /**
   * Read current version from .version file, or default to 1.0.0.
   */
  private readCurrentVersion(): string {
    if (existsSync(this.versionFile)) {
      const content = readFileSync(this.versionFile, 'utf-8').trim()
      return content || '1.0.0'
    }
    return '1.0.0'
  }

  /**
   * Bump patch version (1.0.0 → 1.0.1).
   */
  private bumpPatchVersion(current: string): string {
    const v = this.parseVersion(current)
    v.patch += 1
    return this.formatVersion(v)
  }

  /**
   * Get the next version and persist it to .version file.
   */
  getNextVersion(): string {
    const current = this.readCurrentVersion()
    const next = this.bumpPatchVersion(current)
    writeFileSync(this.versionFile, next, 'utf-8')
    this.config.logger.info(`Version bumped: ${current} → ${next}`)
    return next
  }

  /**
   * Generate package.json content for the npm package.
   */
  generatePackageJson(version: string): Record<string, unknown> {
    return {
      name: this.config.packageName,
      version,
      type: 'module',
      private: false,
      description: `Static data export for ${this.config.projectKeys.join(', ')} (MWNF)`,
      keywords: ['mwnf', 'museum', 'islamic-art', 'data'],
      author: 'Museum With No Frontiers',
      license: 'MIT',
      repository: {
        type: 'git',
        url: 'https://github.com/mwnf/inventory-app',
      },
      main: './manifest.json',
      exports: {
        '.': './manifest.json',
        './*.json': './*.json',
        './translations/*': './translations/*',
      },
      files: [
        '*.json',
        '*.json.gz',
        'translations/',
        'README.md',
      ],
      engines: {
        node: '>=16.0.0',
        npm: '>=8.0.0',
      },
    }
  }

  /**
   * Generate README.md content for the npm package.
   */
  generateReadme(packageName: string): string {
    const projectList = this.config.projectKeys.join(', ')
    return `# ${packageName}

Static data export for the Museum With No Frontiers Inventory API.

**Projects included:** ${projectList}

## Installation

\`\`\`bash
npm install ${packageName}
\`\`\`

## Usage

### Import manifest (with metadata)
\`\`\`javascript
import manifest from '${packageName}/manifest.json' assert { type: 'json' }

console.log(manifest.generatedAt)
console.log(manifest.languages) // ['en', 'ar', 'fr', ...]
\`\`\`

### Import items (objects & monuments)
\`\`\`javascript
import items from '${packageName}/items.json' assert { type: 'json' }

items.forEach(item => {
  console.log(item.id, item.type, item.translations)
})
\`\`\`

### Import translated items for a specific language
\`\`\`javascript
import itemsAr from '${packageName}/items.ar.json' assert { type: 'json' }
import itemsEn from '${packageName}/items.en.json' assert { type: 'json' }
import itemsFr from '${packageName}/items.fr.json' assert { type: 'json' }
\`\`\`

### Import other data
\`\`\`javascript
import partners from '${packageName}/partners.json' assert { type: 'json' }
import collections from '${packageName}/collections.json' assert { type: 'json' }
import dynasties from '${packageName}/dynasties.json' assert { type: 'json' }
import countries from '${packageName}/countries.json' assert { type: 'json' }
import glossary from '${packageName}/glossary.json' assert { type: 'json' }
import languages from '${packageName}/languages.json' assert { type: 'json' }
\`\`\`

### Import translated reference data
Each reference data file has translated variants (e.g., \`partners.ar.json\`, \`countries.en.json\`):

\`\`\`javascript
import partnersAr from '${packageName}/partners.ar.json' assert { type: 'json' }
import partnersEn from '${packageName}/partners.en.json' assert { type: 'json' }
import countriesAr from '${packageName}/countries.ar.json' assert { type: 'json' }
\`\`\`

## Data Format

Each JSON file contains denormalized data for a specific entity type.

- **Base files** (e.g., \`items.json\`): All items with translations nested by language code
- **Language-specific files** (e.g., \`items.en.json\`): Flattened translations for a single language
- **Translations directory**: Contains all translation files organized by entity and language

Images reference public URLs (e.g., \`https://inventory.metanull.eu/pub/...\`).

## Notes

- This package contains **read-only, static data** extracted from the Inventory API.
- It is **not** the authoritative API — consult the API directly for real-time data.
- Data is regenerated periodically and published as a new version.
- For inquiries or issues, contact the Museum With No Frontiers team.

## License

MIT
`
  }
}
