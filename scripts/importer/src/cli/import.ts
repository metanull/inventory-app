#!/usr/bin/env node
/**
 * Unified Legacy Import CLI
 *
 * This is the main entry point for the unified import system.
 * Currently implements SQL-based import strategy.
 *
 * Usage:
 *   npm run import [options]
 *
 * Options:
 *   --dry-run          Simulate import without writing data
 *   --start-at <name>  Start from specific importer
 *   --stop-at <name>   Stop at specific importer
 *   --only <name>      Run only the specified importer
 *   --list-importers   List all available importers
 */

import dotenv from 'dotenv';
import { resolve } from 'path';
import { Command } from 'commander';
import chalk from 'chalk';
import mysql from 'mysql2/promise';

import { UnifiedTracker } from '../core/tracker.js';
import { SqlWriteStrategy } from '../strategies/sql-strategy.js';
import type { ImportContext, ILegacyDatabase } from '../core/base-importer.js';
import type { ImportResult } from '../core/types.js';

import {
  LanguageImporter,
  LanguageTranslationImporter,
  CountryImporter,
  CountryTranslationImporter,
  ProjectImporter,
  PartnerImporter,
  ObjectImporter,
  MonumentImporter,
} from '../importers/index.js';

// Load environment variables
dotenv.config({ path: resolve(process.cwd(), '.env') });

// Importer registry
interface ImporterConfig {
  key: string;
  name: string;
  description: string;
  importerClass: new (context: ImportContext) => { import(): Promise<ImportResult>; getName(): string };
  dependencies?: string[];
}

const ALL_IMPORTERS: ImporterConfig[] = [
  // Phase 0: Reference Data
  {
    key: 'language',
    name: 'Languages',
    description: 'Import language reference data',
    importerClass: LanguageImporter,
    dependencies: [],
  },
  {
    key: 'language-translation',
    name: 'Language Translations',
    description: 'Import language name translations',
    importerClass: LanguageTranslationImporter,
    dependencies: ['language'],
  },
  {
    key: 'country',
    name: 'Countries',
    description: 'Import country reference data',
    importerClass: CountryImporter,
    dependencies: [],
  },
  {
    key: 'country-translation',
    name: 'Country Translations',
    description: 'Import country name translations',
    importerClass: CountryTranslationImporter,
    dependencies: ['language', 'country'],
  },
  // Phase 1: Core Data
  {
    key: 'project',
    name: 'Projects',
    description: 'Import projects (creates Context, Collection, Project)',
    importerClass: ProjectImporter,
    dependencies: ['language'],
  },
  {
    key: 'partner',
    name: 'Partners',
    description: 'Import museums and institutions',
    importerClass: PartnerImporter,
    dependencies: ['project', 'language', 'country'],
  },
  {
    key: 'object',
    name: 'Objects',
    description: 'Import object items',
    importerClass: ObjectImporter,
    dependencies: ['project', 'partner', 'language'],
  },
  {
    key: 'monument',
    name: 'Monuments',
    description: 'Import monument items',
    importerClass: MonumentImporter,
    dependencies: ['project', 'partner', 'language'],
  },
];

/**
 * Simple Legacy Database wrapper
 */
class LegacyDatabase implements ILegacyDatabase {
  private connection: mysql.Connection | null = null;
  private config: mysql.ConnectionOptions;

  constructor() {
    this.config = {
      host: process.env['LEGACY_DB_HOST'] || 'localhost',
      port: parseInt(process.env['LEGACY_DB_PORT'] || '3306', 10),
      user: process.env['LEGACY_DB_USER'] || 'root',
      password: process.env['LEGACY_DB_PASSWORD'] || '',
      database: process.env['LEGACY_DB_DATABASE'] || 'mwnf3',
      multipleStatements: true,
    };
  }

  async connect(): Promise<void> {
    this.connection = await mysql.createConnection(this.config);
  }

  async disconnect(): Promise<void> {
    if (this.connection) {
      await this.connection.end();
      this.connection = null;
    }
  }

  async query<T>(sql: string): Promise<T[]> {
    if (!this.connection) {
      throw new Error('Database not connected');
    }
    const [rows] = await this.connection.execute(sql);
    return rows as T[];
  }
}

/**
 * Create connection to new database
 */
async function createNewDbConnection(): Promise<mysql.Connection> {
  return mysql.createConnection({
    host: process.env['DB_HOST'] || 'localhost',
    port: parseInt(process.env['DB_PORT'] || '3306', 10),
    user: process.env['DB_USERNAME'] || 'root',
    password: process.env['DB_PASSWORD'] || '',
    database: process.env['DB_DATABASE'] || 'inventory',
  });
}

/**
 * Determine if an importer should run
 */
function shouldRunImporter(
  config: ImporterConfig,
  only: string | undefined,
  startAt: string | undefined,
  stopAt: string | undefined
): boolean {
  if (only) {
    return config.key === only;
  }

  const importerIndex = ALL_IMPORTERS.findIndex((i) => i.key === config.key);

  if (startAt) {
    const startIndex = ALL_IMPORTERS.findIndex((i) => i.key === startAt);
    if (startIndex === -1) {
      throw new Error(`Unknown importer: ${startAt}`);
    }
    if (importerIndex < startIndex) {
      return false;
    }
  }

  if (stopAt) {
    const stopIndex = ALL_IMPORTERS.findIndex((i) => i.key === stopAt);
    if (stopIndex === -1) {
      throw new Error(`Unknown importer: ${stopAt}`);
    }
    if (importerIndex > stopIndex) {
      return false;
    }
  }

  return true;
}

// CLI
const program = new Command();

program
  .name('importer')
  .description('Unified Legacy Import Tool - Imports data from legacy database')
  .version('1.0.0');

program
  .command('import')
  .description('Run import process')
  .option('--dry-run', 'Simulate import without writing data', false)
  .option('--start-at <importer>', 'Start from specific importer')
  .option('--stop-at <importer>', 'Stop at specific importer')
  .option('--only <importer>', 'Run only the specified importer')
  .option('--list-importers', 'List all available importers')
  .action(async (options) => {
    try {
      const dryRun = options.dryRun === true;
      const startAt = options.startAt;
      const stopAt = options.stopAt;
      const only = options.only;
      const listImporters = options.listImporters === true;

      // Handle --list-importers
      if (listImporters) {
        console.log(chalk.bold('\nAvailable Importers:\n'));
        ALL_IMPORTERS.forEach((imp, idx) => {
          console.log(`  ${(idx + 1).toString().padStart(2)}. ${imp.key.padEnd(22)} - ${imp.description}`);
        });
        console.log('\nUsage examples:');
        console.log('  npm run import                          # Run all importers');
        console.log('  npm run import -- --start-at project    # Start from project onwards');
        console.log('  npm run import -- --stop-at partner     # Run up to and including partner');
        console.log('  npm run import -- --only partner        # Run only partner');
        console.log('');
        process.exit(0);
      }

      console.log(chalk.bold('='.repeat(80)));
      console.log(chalk.bold.cyan('UNIFIED LEGACY IMPORT'));
      console.log(chalk.bold('='.repeat(80)));
      console.log(chalk.gray(`Start time: ${new Date().toISOString()}`));
      console.log(chalk.gray(`Dry-run: ${dryRun ? 'YES' : 'NO'}`));
      if (startAt) console.log(chalk.gray(`Start at: ${startAt}`));
      if (stopAt) console.log(chalk.gray(`Stop at: ${stopAt}`));
      if (only) console.log(chalk.gray(`Only: ${only}`));
      console.log('');

      // Connect to databases
      console.log(chalk.cyan('Connecting to databases...'));
      const legacyDb = new LegacyDatabase();
      await legacyDb.connect();
      console.log(chalk.green('✓ Legacy database connected'));

      const newDb = await createNewDbConnection();
      console.log(chalk.green('✓ New database connected'));

      // Initialize tracker and strategy
      const tracker = new UnifiedTracker();
      const strategy = new SqlWriteStrategy(newDb, tracker);

      // Create import context
      const importContext: ImportContext = {
        legacyDb,
        strategy,
        tracker,
        dryRun,
      };

      // Track results
      const results = new Map<string, ImportResult>();
      const startTime = Date.now();

      // Execute importers
      for (const config of ALL_IMPORTERS) {
        const shouldRun = shouldRunImporter(config, only, startAt, stopAt);

        if (!shouldRun) {
          console.log(chalk.gray(`⏭  Skipping ${config.name}`));
          continue;
        }

        console.log(chalk.cyan(`\n▶  Starting ${config.name}...`));

        try {
          const importer = new config.importerClass(importContext);
          const result = await importer.import();
          results.set(config.key, result);

          if (result.errors.length > 0) {
            console.log(chalk.red(`   ❌ ${config.name} completed with ${result.errors.length} errors`));
            // Show first few errors
            result.errors.slice(0, 5).forEach((err) => {
              console.log(chalk.red(`      - ${err}`));
            });
            if (result.errors.length > 5) {
              console.log(chalk.red(`      ... and ${result.errors.length - 5} more errors`));
            }
          } else {
            console.log(chalk.green(`   ✓ ${config.name} completed: ${result.imported} imported, ${result.skipped} skipped`));
          }

          // Report warnings
          if (result.warnings && result.warnings.length > 0) {
            console.log(chalk.yellow(`   ⚠  ${result.warnings.length} warnings`));
          }
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          console.log(chalk.red(`   ❌ ${config.name} failed: ${message}`));
          results.set(config.key, {
            success: false,
            imported: 0,
            skipped: 0,
            errors: [message],
          });
        }
      }

      // Calculate totals
      const totals = Array.from(results.values()).reduce(
        (acc, r) => ({
          imported: acc.imported + r.imported,
          skipped: acc.skipped + r.skipped,
          errors: acc.errors + r.errors.length,
          warnings: acc.warnings + (r.warnings?.length || 0),
        }),
        { imported: 0, skipped: 0, errors: 0, warnings: 0 }
      );

      const duration = Date.now() - startTime;

      // Summary
      console.log(chalk.bold('\n' + '='.repeat(80)));
      console.log(chalk.bold.cyan('IMPORT SUMMARY'));
      console.log(chalk.bold('='.repeat(80)));
      console.log(chalk.green(`✓ Imported: ${totals.imported}`));
      console.log(chalk.gray(`⏭ Skipped:  ${totals.skipped}`));
      if (totals.errors > 0) {
        console.log(chalk.red(`❌ Errors:   ${totals.errors}`));
      }
      if (totals.warnings > 0) {
        console.log(chalk.yellow(`⚠  Warnings: ${totals.warnings}`));
      }
      console.log(chalk.gray(`⏱  Duration: ${(duration / 1000).toFixed(2)}s`));
      console.log('');

      // Cleanup
      await legacyDb.disconnect();
      await newDb.end();

      if (totals.errors > 0) {
        process.exit(1);
      }
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      console.error(chalk.red(`\nFatal error: ${message}`));
      if (error instanceof Error && error.stack) {
        console.error(chalk.gray(error.stack));
      }
      process.exit(1);
    }
  });

program
  .command('validate')
  .description('Validate database connections')
  .action(async () => {
    console.log(chalk.cyan('Validating connections...\n'));

    let hasErrors = false;

    // Legacy database
    try {
      console.log('Testing legacy database connection...');
      const legacyDb = new LegacyDatabase();
      await legacyDb.connect();
      console.log(chalk.green('✓ Legacy database connection successful'));
      await legacyDb.disconnect();
    } catch (error) {
      hasErrors = true;
      const message = error instanceof Error ? error.message : String(error);
      console.log(chalk.red(`❌ Legacy database connection failed: ${message}`));
    }

    // New database
    try {
      console.log('Testing new database connection...');
      const newDb = await createNewDbConnection();
      console.log(chalk.green('✓ New database connection successful'));
      await newDb.end();
    } catch (error) {
      hasErrors = true;
      const message = error instanceof Error ? error.message : String(error);
      console.log(chalk.red(`❌ New database connection failed: ${message}`));
    }

    if (hasErrors) {
      console.log(chalk.red('\n❌ Validation failed. Fix errors above before importing.'));
      process.exit(1);
    } else {
      console.log(chalk.green('\n✓ All connections validated successfully.'));
    }
  });

program.parse();
