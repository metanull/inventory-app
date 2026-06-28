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
 *   npm run export -- islamicart ISL
 *   npm run export -- combined ISL WHS --force
 *   npm run export -- islamicart ISL --output-dir /tmp/export --base-url https://cdn.example.com/storage
 */

import dotenv from 'dotenv';
import { resolve } from 'path';
import { existsSync, mkdirSync, rmSync } from 'fs';
import { Command } from 'commander';
import chalk from 'chalk';

import { Database } from '../core/database.js';
import { Logger } from '../core/logger.js';
import type { ExportContext } from '../core/types.js';
import {
  ManifestExporter,
  LanguageExporter,
  CountryExporter,
  DynastyExporter,
  PartnerExporter,
  ItemExporter,
  CollectionExporter,
  GlossaryExporter,
} from '../exporters/index.js';

dotenv.config({ path: resolve(process.cwd(), '.env') });

const program = new Command();

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
    process.env['BASE_URL'] ?? 'https://inventory.metanull.eu/storage'
  )
  .action(async (subdirectory: string, projectKeys: string[], options: { force: boolean; outputDir: string; baseUrl: string }) => {
    const logger = new Logger('Exporter');

    console.log(chalk.bold('='.repeat(70)));
    console.log(chalk.bold.cyan('MWNF STATIC DATA EXPORTER'));
    console.log(chalk.bold('='.repeat(70)));
    console.log(chalk.gray(`Start time:    ${new Date().toISOString()}`));
    console.log(chalk.gray(`Project keys:  ${projectKeys.join(', ')}`));
    console.log(chalk.gray(`Subdirectory:  ${subdirectory}`));
    console.log(chalk.gray(`Force:         ${options.force ? 'YES' : 'NO'}`));
    console.log('');

    const outputBaseDir = resolve(process.cwd(), options.outputDir);
    const outputDir = resolve(outputBaseDir, subdirectory);

    // Guard: fail if output directory exists and --force not given
    if (existsSync(outputDir)) {
      if (!options.force) {
        console.error(chalk.red(`\nOutput directory already exists: ${outputDir}`));
        console.error(chalk.red('Use --force to overwrite it.\n'));
        process.exit(1);
      }
      logger.warning(`Removing existing output directory (--force): ${outputDir}`);
      rmSync(outputDir, { recursive: true, force: true });
    }

    mkdirSync(outputDir, { recursive: true });
    logger.info(`Output directory: ${outputDir}`);

    // Connect to the inventory database
    const db = new Database();

    try {
      logger.info('Connecting to database...');
      await db.connect();
      console.log(chalk.green('  ✓ Database connected'));

      // Resolve project IDs from the provided backward_compatibility keys
      logger.info(`Resolving project IDs for: ${projectKeys.join(', ')}`);
      const projectIds = await db.resolveProjectIds(projectKeys);
      console.log(chalk.green(`  ✓ Found ${projectIds.length} project(s)`));
      console.log('');

      const context: ExportContext = {
        db,
        outputDir,
        projectIds,
        projectKeys,
        baseUrl: options.baseUrl,
        logger,
      };

      const exporters = [
        new ManifestExporter(context),
        new LanguageExporter(context),
        new CountryExporter(context),
        new DynastyExporter(context),
        new PartnerExporter(context),
        new ItemExporter(context),
        new CollectionExporter(context),
        new GlossaryExporter(context),
      ];

      const results = [];
      for (const exporter of exporters) {
        try {
          const result = await exporter.export();
          results.push({ name: exporter.getName(), ...result, error: null });
        } catch (err) {
          const message = err instanceof Error ? err.message : String(err);
          logger.error(`${exporter.getName()} failed: ${message}`);
          results.push({ name: exporter.getName(), file: '', count: 0, error: message });
        }
      }

      const hasErrors = results.some((r) => r.error !== null);

      console.log('');
      console.log(chalk.bold('='.repeat(70)));
      if (hasErrors) {
        console.log(chalk.bold.red('EXPORT COMPLETED WITH ERRORS'));
      } else {
        console.log(chalk.bold.green('EXPORT COMPLETED'));
      }
      console.log(chalk.gray(`End time: ${new Date().toISOString()}`));
      console.log(chalk.gray(`Output:   ${outputDir}`));
      console.log('');

      for (const r of results) {
        if (r.error) {
          console.log(chalk.red(`  ✗ ${r.name}: ${r.error}`));
        } else {
          console.log(chalk.green(`  ✓ ${r.file} (${r.count})`));
        }
      }

      console.log(chalk.bold('='.repeat(70)));

      process.exit(hasErrors ? 1 : 0);
    } catch (err) {
      const message = err instanceof Error ? err.message : String(err);
      console.error(chalk.red(`\nFatal error: ${message}`));
      if (err instanceof Error && err.stack) {
        console.error(chalk.gray(err.stack));
      }
      process.exit(1);
    } finally {
      await db.disconnect();
    }
  });

program.parse();
