#!/usr/bin/env node
/**
 * Static JSON Exporter CLI
 *
 * Reads the inventory database and writes a set of denormalized JSON files
 * for consumption by frontend applications (e.g., the new Discover Islamic Art site).
 *
 * Usage:
 *   npm run export -- <subdirectory> <project-key> [more-project-keys...]
 *
 * Examples:
 *   # Export ISL project data only
 *   npm run export -- islamicart ISL
 *
 *   # Export multiple projects
 *   npm run export -- combined ISL WHS --force
 *
 *   # Custom output and base URLs
 *   npm run export -- islamicart ISL --output-dir /tmp/export --base-url https://cdn.example.com/storage
 *
 *   # Export and prepare for npm publishing (auto-increment version, generate package.json)
 *   npm run export -- islamicart ISL --publish
 *   # Then: cd output/islamicart && npm publish
 *
 *   # Publish with custom package name
 *   npm run export -- islamicart ISL --publish --package-name @mwnf/islamic-art-data
 */

import dotenv from 'dotenv'
import { resolve } from 'path'
import { existsSync, mkdirSync, rmSync, writeFileSync } from 'fs'
import { Command } from 'commander'
import chalk from 'chalk'

import { Database } from '../core/database.js'
import { Logger } from '../core/logger.js'
import { PublishManager } from '../core/publish-manager.js'
import type { ExportContext } from '../core/types.js'
import {
  ManifestExporter,
  LanguageExporter,
  CountryExporter,
  DynastyExporter,
  PartnerExporter,
  ItemExporter,
  CollectionExporter,
  GlossaryExporter,
} from '../exporters/index.js'

dotenv.config({ path: resolve(process.cwd(), '.env') })

const program = new Command()

program
  .name('exporter')
  .description('Static JSON data exporter for MWNF public websites')
  .version('1.0.0')
  .argument('<subdirectory>', 'Name of the output subdirectory to create')
  .argument('<project-keys...>', 'One or more legacy project keys to export (e.g., ISL WHS)')
  .option('--force', 'Overwrite output directory if it already exists', false)
  .option('--output-dir <path>', 'Base output directory (relative to cwd or absolute)', 'output')
  .option(
    '--base-url <url>',
    'Base URL for media files',
    process.env['BASE_URL'] ?? './images'
  )
  .option('--publish', 'Generate npm package.json, bump version, and publish to registry', false)
  .option(
    '--package-name <name>',
    'NPM package name (defaults to @mwnf/{subdirectory}-data)',
    ''
  )
  .option(
    '--package-version <semver>',
    'Set an explicit version instead of auto-incrementing (e.g. 1.0.4)'
  )
  .option(
    '--npm-registry <url>',
    'npm registry URL for publish (overrides NPM_REGISTRY env var)'
  )
  .action(
    async (
      subdirectory: string,
      projectKeys: string[],
      options: {
        force: boolean
        outputDir: string
        baseUrl: string
        publish: boolean
        packageName: string
        packageVersion?: string
        npmRegistry?: string
      }
    ) => {
      const logger = new Logger('Exporter')

      console.log(chalk.bold('='.repeat(70)))
      console.log(chalk.bold.cyan('MWNF STATIC DATA EXPORTER'))
      console.log(chalk.bold('='.repeat(70)))
      console.log(chalk.gray(`Start time:    ${new Date().toISOString()}`))
      console.log(chalk.gray(`Project keys:  ${projectKeys.join(', ')}`))
      console.log(chalk.gray(`Subdirectory:  ${subdirectory}`))
      console.log(chalk.gray(`Force:         ${options.force ? 'YES' : 'NO'}`))
      console.log('')

      const outputBaseDir = resolve(process.cwd(), options.outputDir)
      const outputDir = resolve(outputBaseDir, subdirectory)

      // Guard: fail if output directory exists and --force not given
      if (existsSync(outputDir)) {
        if (!options.force) {
          console.error(chalk.red(`\nOutput directory already exists: ${outputDir}`))
          console.error(chalk.red('Use --force to overwrite it.\n'))
          process.exit(1)
        }
        logger.warning(`Removing existing output directory (--force): ${outputDir}`)
        rmSync(outputDir, { recursive: true, force: true })
      }

      mkdirSync(outputDir, { recursive: true })
      logger.info(`Output directory: ${outputDir}`)

      // Connect to the inventory database
      const db = new Database()

      try {
        logger.info('Connecting to database...')
        await db.connect()
        console.log(chalk.green('  ✓ Database connected'))

        // Resolve project IDs from the provided backward_compatibility keys
        logger.info(`Resolving project IDs for: ${projectKeys.join(', ')}`)
        const projectIds = await db.resolveProjectIds(projectKeys)
        console.log(chalk.green(`  ✓ Found ${projectIds.length} project(s)`))
        console.log('')

        const context: ExportContext = {
          db,
          outputDir,
          projectIds,
          projectKeys,
          baseUrl: options.baseUrl,
          logger,
        }

        const exporters = [
          new ManifestExporter(context),
          new LanguageExporter(context),
          new CountryExporter(context),
          new DynastyExporter(context),
          new PartnerExporter(context),
          new ItemExporter(context),
          new CollectionExporter(context),
          new GlossaryExporter(context),
        ]

        const results = []
        for (const exporter of exporters) {
          try {
            const result = await exporter.export()
            results.push({ name: exporter.getName(), ...result, error: null })
          } catch (err) {
            const message = err instanceof Error ? err.message : String(err)
            logger.error(`${exporter.getName()} failed: ${message}`)
            results.push({ name: exporter.getName(), file: '', count: 0, error: message })
          }
        }

        const hasErrors = results.some(r => r.error !== null)

        // Handle npm package publishing if --publish flag is set
        if (options.publish && !hasErrors) {
          console.log('')
          console.log(chalk.bold('='.repeat(70)))
          console.log(chalk.bold.cyan('PUBLISHING NPM PACKAGE'))
          console.log(chalk.bold('='.repeat(70)))

          try {
            const packageName = options.packageName || `@mwnf/${subdirectory}-data`
            const registry =
              options.npmRegistry ||
              process.env['NPM_REGISTRY'] ||
              'https://npm.pkg.github.com'

            // Version file lives next to the output base dir, NOT inside the project
            // output directory, so it survives --force cleans.
            const versionFile = resolve(outputBaseDir, `.version-${subdirectory}`)

            const publishManager = new PublishManager({
              outputDir,
              versionFile,
              packageName,
              projectKeys,
              logger,
              author: process.env['PACKAGE_AUTHOR'],
              license: process.env['PACKAGE_LICENSE'],
              repositoryUrl: process.env['PACKAGE_REPO_URL'],
              registry,
            })

            const nextVersion = options.packageVersion
              ? publishManager.setVersion(options.packageVersion)
              : publishManager.getNextVersion()
            console.log(chalk.green(`  ✓ Version: ${nextVersion}`))

            const packageJson = publishManager.generatePackageJson(nextVersion)
            const packageJsonPath = resolve(outputDir, 'package.json')
            writeFileSync(packageJsonPath, JSON.stringify(packageJson, null, 2), 'utf-8')
            console.log(chalk.green(`  ✓ Generated: package.json`))

            const readmePath = resolve(outputDir, 'README.md')
            const readmeContent = publishManager.generateReadme(packageName)
            writeFileSync(readmePath, readmeContent, 'utf-8')
            console.log(chalk.green(`  ✓ Generated: README.md`))

            console.log('')
            publishManager.publish()
            console.log(chalk.green(`  ✓ Published: ${packageName}@${nextVersion}`))
            console.log('')
          } catch (err) {
            const message = err instanceof Error ? err.message : String(err)
            console.error(chalk.red(`\nPublish failed: ${message}`))
            process.exit(1)
          }
        }

        console.log('')
        console.log(chalk.bold('='.repeat(70)))
        if (hasErrors) {
          console.log(chalk.bold.red('EXPORT COMPLETED WITH ERRORS'))
        } else {
          console.log(chalk.bold.green('EXPORT COMPLETED'))
        }
        console.log(chalk.gray(`End time: ${new Date().toISOString()}`))
        console.log(chalk.gray(`Output:   ${outputDir}`))
        console.log('')

        for (const r of results) {
          if (r.error) {
            console.log(chalk.red(`  ✗ ${r.name}: ${r.error}`))
          } else {
            console.log(chalk.green(`  ✓ ${r.file} (${r.count})`))
          }
        }

        console.log(chalk.bold('='.repeat(70)))

        process.exit(hasErrors ? 1 : 0)
      } catch (err) {
        const message = err instanceof Error ? err.message : String(err)
        console.error(chalk.red(`\nFatal error: ${message}`))
        if (err instanceof Error && err.stack) {
          console.error(chalk.gray(err.stack))
        }
        process.exit(1)
      } finally {
        await db.disconnect()
      }
    }
  )

program.parse()
