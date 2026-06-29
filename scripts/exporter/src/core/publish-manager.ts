import { readFileSync, writeFileSync, existsSync } from 'fs'
import { spawnSync } from 'child_process'
import { Logger } from './logger.js'

export interface PublishConfig {
  outputDir: string
  /** Path to the version counter file — must live OUTSIDE outputDir so --force doesn't reset it. */
  versionFile: string
  packageName: string
  projectKeys: string[]
  logger: Logger
  // Package metadata — all optional; omitted fields are left out of package.json
  author?: string
  license?: string
  repositoryUrl?: string
  // Publishing
  registry?: string
}

export interface SemanticVersion {
  major: number
  minor: number
  patch: number
}

export class PublishManager {
  private config: PublishConfig

  constructor(config: PublishConfig) {
    this.config = config
  }

  private parseVersion(versionString: string): SemanticVersion {
    const parts = versionString.trim().split('.')
    if (parts.length !== 3) {
      throw new Error(`Invalid version format: ${versionString} (expected X.Y.Z)`)
    }
    const [major, minor, patch] = parts.map(Number)
    if (isNaN(major) || isNaN(minor) || isNaN(patch)) {
      throw new Error(`Invalid version format: ${versionString} (parts must be numeric)`)
    }
    return { major, minor, patch }
  }

  private formatVersion(v: SemanticVersion): string {
    return `${v.major}.${v.minor}.${v.patch}`
  }

  private readCurrentVersion(): string {
    if (existsSync(this.config.versionFile)) {
      const content = readFileSync(this.config.versionFile, 'utf-8').trim()
      return content || '1.0.0'
    }
    return '1.0.0'
  }

  /**
   * Bump patch (1.0.3 → 1.0.4) and persist to versionFile.
   * The file lives outside outputDir so it survives --force runs.
   */
  getNextVersion(): string {
    const current = this.readCurrentVersion()
    const v = this.parseVersion(current)
    v.patch += 1
    const next = this.formatVersion(v)
    writeFileSync(this.config.versionFile, next, 'utf-8')
    this.config.logger.info(`Version bumped: ${current} → ${next}`)
    return next
  }

  /**
   * Set an explicit version and persist it.
   * Use when the auto-incremented value is wrong (e.g. first run after version file was lost).
   */
  setVersion(version: string): string {
    this.parseVersion(version)
    writeFileSync(this.config.versionFile, version, 'utf-8')
    this.config.logger.info(`Version set: ${version}`)
    return version
  }

  generatePackageJson(version: string): Record<string, unknown> {
    const pkg: Record<string, unknown> = {
      name: this.config.packageName,
      version,
      type: 'module',
      private: false,
      description: `Static data export for ${this.config.projectKeys.join(', ')}`,
      license: this.config.license ?? 'UNLICENSED',
      main: './manifest.json',
      exports: {
        '.': './manifest.json',
        './*.json': './*.json',
        './translations/*': './translations/*',
      },
      // Explicitly list .json only — .gz companion files are not useful to consumers
      files: [
        '*.json',
        'translations/*.json',
        'README.md',
      ],
      engines: {
        node: '>=16.0.0',
        npm: '>=8.0.0',
      },
    }

    if (this.config.author) pkg['author'] = this.config.author
    if (this.config.repositoryUrl) pkg['repository'] = { type: 'git', url: this.config.repositoryUrl }

    return pkg
  }

  generateReadme(packageName: string): string {
    const projectList = this.config.projectKeys.join(', ')
    const installLine = this.config.registry
      ? `npm install ${packageName} --registry ${this.config.registry}`
      : `npm install ${packageName}`

    return `# ${packageName}

Static data export — projects: ${projectList}.

## Installation

\`\`\`bash
${installLine}
\`\`\`

## Usage

\`\`\`javascript
import manifest from '${packageName}/manifest.json' assert { type: 'json' }
import items    from '${packageName}/items.json'    assert { type: 'json' }

// Lazy-load translations for a language
const { default: translations } = await import(\`${packageName}/translations/items.\${lang}.json\`)
\`\`\`

Available top-level JSON files: \`manifest.json\`, \`items.json\`, \`partners.json\`,
\`collections.json\`, \`dynasties.json\`, \`countries.json\`, \`glossary.json\`, \`languages.json\`.

Each has a per-language translation file under \`translations/{entity}.{lang}.json\`.
`
  }

  /**
   * Run `npm publish` inside outputDir.
   * Throws if the publish command exits with a non-zero status.
   */
  publish(): void {
    const args = ['publish']
    if (this.config.registry) {
      args.push('--registry', this.config.registry)
    }

    this.config.logger.info(`Running: npm ${args.join(' ')}`)

    const result = spawnSync('npm', args, {
      cwd: this.config.outputDir,
      stdio: 'inherit',
      env: process.env,
      shell: true,
    })

    if (result.error) {
      throw new Error(`Failed to spawn npm: ${result.error.message}`)
    }
    if (result.status !== 0) {
      throw new Error(`npm publish exited with code ${result.status}`)
    }
  }
}
